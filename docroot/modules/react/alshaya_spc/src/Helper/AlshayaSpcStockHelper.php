<?php

namespace Drupal\alshaya_spc\Helper;

use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_spc\EventSubscriber\StockEventSubscriber;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class Alshaya Spc Stock Helper.
 *
 * @package Drupal\alshaya_spc\Helper
 */
class AlshayaSpcStockHelper {
  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * AlshayaSpcStockHelper constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Factory service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->logger = $logger_factory->get('alshaya_spc');
  }

  /**
   * Refresh stock cache and Drupal cache of products in cart.
   *
   * @param mixed $cart
   *   Cart data.
   *
   * @return array
   *   Response data.
   */
  public function refreshStockForProductsInCart($cart = NULL) {
    // If empty, simply return.
    if (empty($cart) || empty($cart['items'])) {
      return [];
    }

    $skus = array_column($cart['items'], 'sku');

    $skus_quantity = [];
    foreach ($skus as $sku) {
      // This will trigger force refresh of stock.
      $skus_quantity[$sku] = 0;
    }
    return $this->refreshStockForSkus($skus_quantity);
  }

  /**
   * Refreshes stock for a set of skus.
   *
   * @param array $skus_quantity
   *   The associative array of sku => quantity values.
   *
   * @return array
   *   The stock status of all skus or empty array if nothing is updated.
   */
  public function refreshStockForSkus(array $skus_quantity) {
    foreach ($skus_quantity as $sku => $requested_quantity) {
      if ($sku_entity = SKU::loadFromSku($sku)) {
        $plugin = $sku_entity->getPluginInstance();
        $stock = $plugin->getStock($sku);

        if (($stock === 0) || (($requested_quantity > 0) && ($requested_quantity > $stock))) {
          $response['stock'][$sku] = FALSE;
          $this->logger->info('Refresh Stock skipped for SKU: @sku.', [
            '@sku' => $sku,
          ]);
          continue;
        }

        try {
          $statuses = $this->refreshStock($sku_entity);
          foreach ($statuses as $sku => $status) {
            $response['stock'][$sku] = $status;
          }

          // Invalidate cache tags for the SKU.
          StockEventSubscriber::setSkusWithRefreshedStock($sku);
        }
        catch (\Exception $e) {
          // Do nothing.
        }
      }
    }

    return $response ?? [];
  }

  /**
   * Refreshes stock for a particular sku entity.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku_entity
   *   The loaded sku entity.
   *
   * @return array
   *   The stock status of sku.
   *
   * @throws \Exception
   *   Exception is thrown if there is problem connecting with MDC API.
   */
  private function refreshStock(SKUInterface $sku_entity) {
    static $processed_parents = [];
    $parent = $sku_entity->getPluginInstance()->getParentSku($sku_entity);

    // Refresh current sku stock.
    $sku_entity->refreshStock();
    /** @var \Drupal\acq_sku\AcquiaCommerce\SKUPluginBase $plugin */
    $plugin = $sku_entity->getPluginInstance();
    $stock_status[$sku_entity->getSku()] = $plugin->isProductInStock($sku_entity);
    // Refresh parent stock once if exists for cart items.
    if ($parent instanceof SKU && !in_array($parent->getSku(), $processed_parents)) {
      $processed_parents[] = $parent->getSku();
      $parent->refreshStock();
      $plugin = $parent->getPluginInstance();
      $parent_in_stock = $plugin->isProductInStock($parent);
      if ($stock_status[$sku_entity->getSku()]
        && !$parent_in_stock) {
        $stock_status[$sku_entity->getSku()] = FALSE;
      }
    }

    return $stock_status;
  }

}
