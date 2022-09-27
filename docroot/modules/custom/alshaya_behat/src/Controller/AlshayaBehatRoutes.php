<?php

namespace Drupal\alshaya_behat\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('acq_sku.stock_manager'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('http_kernel.basic'),
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
   */
  public function __construct(
    Connection $connection,
    StockManager $stock_manager,
    SkuManager $sku_manager,
    HttpKernel $http_kernel
  ) {
    $this->database = $connection;
    $this->stockManager = $stock_manager;
    $this->skuManager = $sku_manager;
    $this->httpKernel = $http_kernel;
  }

  /**
   * Provides the first in stock product.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to node page if found else redirects to 404 page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function firstInStockProduct() {
    // Query the database to fetch in stock products.
    $query = $this->database->select('acq_sku_field_data', 'afd');
    $query->leftJoin('acq_sku_stock', 'stock', 'stock.sku = afd.sku');
    $query->leftJoin('node__field_skus', 'nfs', 'nfs.field_skus_value = afd.sku');
    $query->condition('quantity', '0', '>');
    $query->fields('afd', ['sku']);
    $skus = $query->distinct()->execute()->fetchCol();

    foreach ($skus as $sku) {
      // Load the sku.
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

      // Fetch the display node for the sku.
      $node = $this->skuManager->getDisplayNode($main_sku);
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

    // If no SKU is found which in stock, then redirect to 404 page.
    throw new NotFoundHttpException();
  }

}
