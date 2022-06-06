<?php

namespace Drupal\alshaya_online_returns\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Helper class for Online Returns.
 *
 * @package Drupal\alshaya_online_returns\Helper
 */
class OnlineReturnsHelper {

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * OnlineReturnsHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Helper to check if Online Returns is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isOnlineReturnsEnabled() {
    return $this->getConfig()->get('status');
  }

  /**
   * Helper to get Cache Tags for Online Returns Config.
   *
   * @return string[]
   *   A set of cache tags.
   */
  public function getCacheTags() {
    return $this->getConfig()->getCacheTags();
  }

  /**
   * Wrapper function to check if SKU is returnable.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku_entity
   *   SKU entity object.
   *
   * @return bool
   *   SKU is returnable or not.
   */
  public function isSkuReturnable(SKUInterface $sku_entity) {
    $is_returnable = $sku_entity->get('attr_is_returnable')->getString();
    return !($is_returnable != 1);
  }

  /**
   * Wrapper function to check if SKU is a big ticket item.
   *
   * @param \Drupal\acq_commerce\SKUInterface $sku_entity
   *   SKU entity object.
   *
   * @return bool
   *   SKU is big ticket or not.
   */
  public function isSkuBigTicket(SKUInterface $sku_entity) {
    $is_big_ticket = $sku_entity->hasField('attr_big_ticket')
      ? $sku_entity->get('attr_big_ticket')->getString()
      : FALSE;
    return !empty($is_big_ticket);
  }

  /**
   * Wrapper function to prepare order data.
   *
   * @param array $order
   *   Order details array.
   *
   * @return array
   *   Processed order data for online returns.
   */
  public function prepareOrderData(array $order) {
    $paymentMethodDetails = array_filter(
      $order['extension']['payment_additional_info'],
      function ($method) {
        return $method['key'] === 'method_title';
      }
    );
    $paymentMethod = reset($paymentMethodDetails);

    // Check if `is_return_eligible` key exists.
    $return_eligible = NULL;
    if (array_key_exists('is_return_eligible', $order['extension'])) {
      $return_eligible = $order['extension']['is_return_eligible'];
    }
    $order_type = $order['shipping']['extension_attributes']['click_and_collect_type'] ?? '';
    if ($order_type == 'ship_to_store') {
      $return_eligible = FALSE;
    }

    return [
      'orderId' => $order['increment_id'],
      'orderEntityId' => $order['entity_id'],
      'orderCustomerId' => $order['customer_id'],
      'orderType' => $order_type,
      'paymentMethod' => $paymentMethod ? $paymentMethod['value'] : '',
      'isReturnEligible' => $return_eligible,
      'returnExpiration' => $order['extension']['return_expiration'] ?? '',
    ];
  }

  /**
   * Wrapper function to prepare product data.
   *
   * @param array $products
   *   Products array.
   *
   * @return array
   *   Processed product data.
   */
  public function prepareProductsData(array $products) {
    foreach ($products as $key => $item) {
      if (!empty($item['image'])) {
        if ($item['image']['#theme'] == 'image_style') {
          $image_style = $this->entityTypeManager->getStorage('image_style');
          $data = [
            'url' => $image_style->load($item['image']['#style_name'])->buildUrl($item['image']['#uri']),
            'title' => $item['image']['#title'],
            'alt' => $item['image']['#alt'],
          ];
        }
        elseif ($item['is_virtual'] && $item['image']['#theme'] == 'image') {
          $data = [
            'url' => $item['extension_attributes']['product_media'][0]['file'],
            'title' => $item['image']['#alt'],
            'alt' => $item['image']['#alt'],
          ];
        }
        elseif ($item['image']['#theme'] == 'image') {
          $data = [
            'url' => $item['image']['#attributes']['src'],
            'title' => $item['image']['#attributes']['title'],
            'alt' => $item['image']['#attributes']['alt'],
          ];
        }
      }
      $products[$key]['image_data'] = $data ?? NULL;
      $sku = SKU::loadFromSku($item['sku']);
      if ($sku instanceof SKUInterface) {
        $products[$key]['is_returnable'] = $this->isSkuReturnable($sku);
        $products[$key]['is_big_ticket'] = $this->isSkuBigTicket($sku);
      }
      // Update the order total based on the item qty.
      if ($products[$key]['qty_refunded'] > 0
        && $products[$key]['qty_refunded'] <= $products[$key]['qty_ordered']) {
        $products[$key]['qty_ordered'] -= $products[$key]['qty_refunded'];

        // Updating total value as `qty_ordered` is updated.
        $products[$key]['total'] = alshaya_acm_price_format(
          $products[$key]['qty_ordered'] * $products[$key]['price_incl_tax'],
        );
      }
    }
    return $products;
  }

  /**
   * Wrapper function to get Online Returns Config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   Online Returns Config.
   */
  protected function getConfig() {
    static $config;

    if (is_null($config)) {
      $config = $this->configFactory->get('alshaya_online_returns.settings');
    }

    return $config;
  }

}
