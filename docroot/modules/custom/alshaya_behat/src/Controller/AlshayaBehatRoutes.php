<?php

namespace Drupal\alshaya_behat\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Alshaya Behat controller.
 */
class AlshayaBehatRoutes extends ControllerBase {

  /**
   * Database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Stock Manager.
   *
   * @var \Drupal\acq_sku\StockManager
   */
  private $stockManager;

  /**
   * Sku manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  private $skuManager;

  /**
   * HTTP Kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernel
   */
  protected $httpKernel;

  /**
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('acq_sku.stock_manager'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('http_kernel.basic'),
      $container->get('request_stack')
    );
  }

  /**
   * Constructor for AlshayaBehatRoutes.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\acq_sku\StockManager $stock_manager
   *   Stock manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku manager.
   * @param \Symfony\Component\HttpKernel\HttpKernel $http_kernel
   *   Http kernel.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   */
  public function __construct(
    Connection $connection,
    StockManager $stock_manager,
    SkuManager $sku_manager,
    HttpKernel $http_kernel,
    RequestStack $request_stack
  ) {
    $this->database = $connection;
    $this->stockManager = $stock_manager;
    $this->skuManager = $sku_manager;
    $this->httpKernel = $http_kernel;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Access checker for behat requests.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function checkAccess() {
    $behat_key_in_settings = Settings::get('behat_secret_key');
    if (empty($behat_key_in_settings)) {
      return AccessResult::forbidden('Secret key not provided in settings');
    }
    $behat_key_in_url = $this->request->query->get('behat');
    return AccessResult::allowedIf($behat_key_in_settings === $behat_key_in_url);
  }

  /**
   * Provides the first in stock product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to node page if found else redirects to 404 page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function firstInStockProduct() {
    // Query the database to fetch in-stock products.
    $query = $this->database->select('node__field_skus', 'nfs');
    $query->leftJoin('acq_sku_stock', 'stock', 'stock.sku = nfs.field_skus_value');
    $query->condition('quantity', '0', '>');
    $query->fields('stock', ['sku']);
    $skus = $query->distinct()->execute()->fetchCol();

    foreach ($skus as $sku) {
      // Load the SKU.
      $main_sku = SKU::loadFromSku($sku);
      if ($this->stockManager->isProductInStock($main_sku)) {
        // SKU might be configurable. So fetch the parent.
        $parent_sku = $this->skuManager->getParentSkuBySku($main_sku);
        if ($parent_sku) {
          if ($this->stockManager->isProductInStock($parent_sku)) {
            $main_sku = $parent_sku;
          }
          else {
            continue;
          }
        }
      }
      else {
        continue;
      }

      // Fetch the display node for the SKU.
      $node = $this->skuManager->getDisplayNode($main_sku);
      if (is_null($node)) {
        continue;
      }
      // Request the node and check if there is any error when loading the node.
      // If there is an error we check the next sku.
      $request = Request::create('/node/' . $node->id());
      try {
        $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
      }
      catch (\Exception) {
        continue;
      }
      // Redirect to the node page.
      return new RedirectResponse($node->toUrl()->toString());
    }

    // If no SKU is found which is in stock, then redirect to 400 page.
    throw new BadRequestHttpException('No in-stock products found.');
  }

  /**
   * Provides the first OOS product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to node page if found else redirects to 404 page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   */
  public function firstOosProduct() {
    // Query the database to fetch out of stock products.
    $query = $this->database->select('node__field_skus', 'nfs');
    $query->leftJoin('acq_sku_stock', 'stock', 'stock.sku = nfs.field_skus_value');
    $query->condition('quantity', '0', '=');
    $query->fields('stock', ['sku']);
    $skus = $query->distinct()->execute()->fetchCol();

    foreach ($skus as $sku) {
      // Load the sku.
      $main_sku = SKU::loadFromSku($sku);
      if (!$this->stockManager->isProductInStock($main_sku)) {
        // SKU might be configurable. So fetch the parent.
        $parent_sku = $this->skuManager->getParentSkuBySku($main_sku);
        if ($parent_sku) {
          if (!$this->stockManager->isProductInStock($parent_sku)) {
            $main_sku = $parent_sku;
          }
          else {
            continue;
          }
        }
      }
      else {
        continue;
      }

      // Fetch the display node for the sku.
      $node = $this->skuManager->getDisplayNode($main_sku);
      if (is_null($node)) {
        continue;
      }
      // Request the node and check if there is any error when loading the node.
      // If there is an error we check the next sku.
      $request = Request::create('/node/' . $node->id());
      try {
        $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
      }
      catch (\Exception) {
        continue;
      }
      // Redirect to the node page.
      return new RedirectResponse($node->toUrl()->toString());
    }

    // If no SKU is found which is OOS, then redirect to 400 page.
    throw new BadRequestHttpException('No OOS products found.');
  }

}
