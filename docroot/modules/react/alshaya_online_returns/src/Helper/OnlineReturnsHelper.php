<?php

namespace Drupal\alshaya_online_returns\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\acq_commerce\SKUInterface;
use Drupal\acq_sku\Entity\SKU;
use Drupal\alshaya_acm_checkout\CheckoutOptionsManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;
use Drupal\Component\Serialization\Json;

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
   * Checkout Options Manager service object.
   *
   * @var \Drupal\alshaya_acm_checkout\CheckoutOptionsManager
   */
  protected $checkoutOptionsManager;

  /**
   * OnlineReturnsHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\alshaya_acm_checkout\CheckoutOptionsManager $checkout_options_manager
   *   Checkout Options Manager service object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    CheckoutOptionsManager $checkout_options_manager
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->checkoutOptionsManager = $checkout_options_manager;
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
   * Helper to check if Online Returns cart banner is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isOnlineReturnsCartBannerEnabled() {
    return $this->isOnlineReturnsEnabled() && $this->getConfig()->get('cart_banner');
  }

  /**
   * Helper to check if EgiftCard refund is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isEgiftRefundEnabled(): bool {
    return $this->configFactory->get('alshaya_online_returns.egift_card_refund')->get('egift_card_refund_enabled') ?: FALSE;
  }

  /**
   * Helper to get the eGift card refund helper text.
   *
   * @return string
   *   Egift card refund option text.
   */
  public function getEgiftRefundText() {
    if (!$this->isEgiftRefundEnabled()) {
      return '';
    }
    return $this->configFactory->get('alshaya_online_returns.egift_card_refund_config')->get('egift_refund_text') ?: '';
  }

  /**
   * Helper to get the eGift card icon.
   *
   * @return string
   *   Egift card icon.
   */
  public function getEgiftCardIcon() {
    if (!$this->isEgiftRefundEnabled()) {
      return '';
    }

    $fileUri = '';
    if ($this->configFactory->get('alshaya_online_returns.egift_card_refund_config')->get('egift_card_icon')) {
      $cardImage = $this->configFactory->get('alshaya_online_returns.egift_card_refund_config')->get('egift_card_icon');
      $fileUri = File::load($cardImage[0])->getFileUri();
    }
    return $fileUri ?: '';
  }

  /**
   * Helper to get list of not supported payment methods for eGift card refund.
   *
   * @return array
   *   An array containting all the payment methods with enable/disable value.
   */
  public function getNotSupportedEgiftMethodsForOnlineReturns() {
    return $this->configFactory->get('alshaya_online_returns.egift_card_refund')->get('not_supported_refund_payment_methods');
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
   * Helper function to check if the product is a big ticket item.
   *
   * @param array $item
   *   Individual product data from orders API.
   *
   * @return bool
   *   TRUE if product is big ticket else FALSE.
   */
  public function isBigTicketItem(array $item) {
    if (!isset($item['extension_attributes']['product_options'])) {
      return FALSE;
    }
    // We expect one value in the `product_options` attribute which is
    // expected to be a json string.
    $product_options = reset($item['extension_attributes']['product_options']);
    $attribute_options = is_string($product_options)
      ? Json::decode($product_options)
      : NULL;
    // New field `big_ticket` introduced in MDC orders API at item level
    // under extensions attributes, helps in determining whether the ordered
    // item is big ticket or not. If `big_ticket` attribute is missing
    // or "0" it is considered as FALSE else TRUE.
    // @see CORE-55974 for reference.
    return isset($attribute_options['big_ticket'])
      ? (bool) $attribute_options['big_ticket']
      : FALSE;
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
      fn($method) => $method['key'] === 'method_title'
    );
    $paymentMethod = reset($paymentMethodDetails);

    // Check if `is_return_eligible` key exists.
    $return_eligible = NULL;
    if (array_key_exists('is_return_eligible', $order['extension'])) {
      $return_eligible = $order['extension']['is_return_eligible'];
    }
    $order_type = $order['shipping']['method'] ?? '';
    if ($order_type == $this->checkoutOptionsManager->getClickandColectShippingMethod()) {
      $return_eligible = FALSE;
      // Change the order type to cc, so that we can compare it in FE.
      $order_type = 'cc';
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
      }
      // Check if it is big ticket item or not.
      $products[$key]['is_big_ticket'] = $this->isBigTicketItem($item);
      // Update the order total based on the item qty.
      if ($products[$key]['qty_refunded'] > 0
        && $products[$key]['qty_refunded'] <= $products[$key]['qty_ordered']) {
        $products[$key]['qty_ordered'] -= $products[$key]['qty_refunded'];
        // Update the `ordered` flag.
        $products[$key]['ordered'] = $products[$key]['qty_ordered'];

        // Updating total value as `qty_ordered` is updated.
        $products[$key]['total'] = alshaya_acm_price_format(
          $products[$key]['qty_ordered'] * $products[$key]['price_incl_tax'],
        );
      }
    }
    return $products;
  }

  /**
   * Wrapper function to validate if the return request is valid or not.
   *
   * @param array $order_details
   *   An array containing all the order details.
   *
   * @return bool
   *   True if the return request is valid else false.
   */
  public function validateReturnRequest(array $order_details) {
    // Return from here if order type is `cc`.
    if ($order_details['#order']['orderType'] == 'cc') {
      return FALSE;
    }

    // Validate if order is expired or not.
    if ($order_details['#order']['returnExpiration']) {
      // Convert the string to date object and compare the timestamp with
      // current time.
      $return_time = strtotime($order_details['#order']['returnExpiration'] . ' 23:59:59');
      if ($return_time < time()) {
        return FALSE;
      }
    }

    // Validate if order contains big ticket items or not.
    foreach ($order_details['#products'] as $product) {
      if (isset($product['is_big_ticket']) && $product['is_big_ticket']) {
        return FALSE;
      }
    }

    return TRUE;
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
