<?php

namespace Drupal\alshaya_behat\Controller;

use Drupal\acq_sku\Entity\SKU;
use Drupal\acq_sku\StockManager;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\alshaya_behat\Service\AlshayaBehatHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Alshaya Behat controller.
 */
class AlshayaBehatRoutes extends ControllerBase {

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
   * Request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Alshaya behat helper.
   *
   * @var \Drupal\alshaya_behat\Service\AlshayaBehatHelper
   */
  protected $alshayaBehat;

  /**
   * Number of skus to fetch.
   *
   * @var int
   */
  protected const SKUS_LIMIT = 10;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('acq_sku.stock_manager'),
      $container->get('alshaya_acm_product.skumanager'),
      $container->get('request_stack'),
      $container->get('alshaya_behat.helper')
    );
  }

  /**
   * Constructor for AlshayaBehatRoutes.
   *
   * @param \Drupal\acq_sku\StockManager $stock_manager
   *   Stock manager.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   Sku manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\alshaya_behat\Service\AlshayaBehatHelper $alshaya_behat
   *   Alshaya behat.
   */
  public function __construct(
    StockManager $stock_manager,
    SkuManager $sku_manager,
    RequestStack $request_stack,
    AlshayaBehatHelper $alshaya_behat
  ) {
    $this->stockManager = $stock_manager;
    $this->skuManager = $sku_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->alshayaBehat = $alshaya_behat;
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
    $page = 0;

    while (TRUE) {
      // Query the database to fetch in-stock products.
      $skus = $this->alshayaBehat->getSkus($page, self::SKUS_LIMIT);
      if (empty($skus)) {
        break;
      }

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
        // Request the node and check if there is any error when loading the
        // node.
        // If there is an error we check the next sku.
        if (!$this->alshayaBehat->isNodePageLoading($node)) {
          continue;
        }
        // Redirect to the node page.
        return new RedirectResponse($node->toUrl()->toString());
      }

      $page++;
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
    $page = 0;

    while (TRUE) {
      // Query the database to fetch out of stock products.
      $skus = $this->alshayaBehat->getSkus($page, self::SKUS_LIMIT, TRUE);
      if (empty($skus)) {
        break;
      }

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
        // Request the node and check if there is any error when loading the
        // node.
        // If there is an error we check the next sku.
        if (!$this->alshayaBehat->isNodePageLoading($node)) {
          continue;
        }
        // Redirect to the node page.
        return new RedirectResponse($node->toUrl()->toString());
      }

      $page++;
    }

    // If no SKU is found which is OOS, then redirect to 400 page.
    throw new BadRequestHttpException('No OOS products found.');
  }

}
