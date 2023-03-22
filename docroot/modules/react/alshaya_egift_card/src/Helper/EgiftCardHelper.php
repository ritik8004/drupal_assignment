<?php

namespace Drupal\alshaya_egift_card\Helper;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\token\TokenInterface;

/**
 * Helper class for Egift Card.
 *
 * @package Drupal\alshaya_egift_card\Helper
 */
class EgiftCardHelper {
  use StringTranslationTrait;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Token Interface.
   *
   * @var \Drupal\token\TokenInterface
   */
  protected $token;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * EgiftCardHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\token\TokenInterface $token
   *   Token interface.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TokenInterface $token,
    EntityTypeManagerInterface $entity_type_manager,
    CacheBackendInterface $cache_backend
  ) {
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * Helper to check if EgiftCard is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isEgiftCardEnabled() {
    return $this->configFactory->get('alshaya_egift_card.settings')->get('egift_card_enabled');
  }

  /**
   * Helper to check if EgiftCard refund is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isEgiftRefundEnabled(): bool {
    return $this->configFactory->get('alshaya_egift_card.settings')->get('egift_card_refund_enabled') ?: false;
  }

  /**
   * Helper to check if link card for faster payment option for top-up is enabled.
   *
   * @return bool
   *   TRUE/FALSE
   */
  public function isLinkCardForTopupEnabled() {
    return $this->configFactory->get('alshaya_egift_card.settings')->get('link_card_for_topup');
  }

  /**
   * Helper to get list of not supported payment methods for eGift card.
   *
   * @return array
   *   An array containting all the payment methods with enable/disable value.
   */
  public function getNotSupportedPaymentMethods() {
    return $this->configFactory->get('alshaya_egift_card.settings')->get('payment_methods_not_supported');
  }

  /**
   * Helper to terms & condition text for topup card.
   *
   * @return markup
   *   An terms and condition text from configuration.
   */
  public function getTermsAndConditionText() {
    $eGift_status = $this->isEgiftCardEnabled();
    if (!$eGift_status) {
      return '';
    }
    $config = $this->configFactory->get('alshaya_egift_card.settings');
    $term_conditions_text = $config->get('topup_terms_conditions_text') != null
      ? $this->token->replace($config->get('topup_terms_conditions_text')['value'])
      : '';
    return $term_conditions_text;
  }

  /**
   * Helper to get the topup quote expiration time.
   *
   * @return integer
   *   An integer containing the expiration time ( in mins ).
   */
  public function getTopupQuoteExpirationTime() {
    return $this->configFactory->get('alshaya_egift_card.settings')->get('topup_quote_expiration');
  }

  /**
   * Helper to get configuration to allow saved cc for top-up.
   *
   * @return array|false|mixed
   */
  public function getAllowSavedCCForTopUp() {
    $allow_saved_card = $this->configFactory->get('alshaya_egift_card.settings')->get('allow_saved_credit_cards_for_topup');
    return !empty($allow_saved_card);
  }

  /**
   * Helper to check if payment is done by egift card.
   *
   * @param array $order
   *   The order array.
   *
   * @return bool
   *   Return TRUE is payment is done by egift card else FALSE.
   */
  public function partialPaymentDoneByEgiftCard(array $order) {
    return array_key_exists('hps_redeemed_amount', $order['extension']);
  }

  /**
   * Helper to get the egift redemption type from the order.
   *
   * @param array $order
   *   The order array.
   *
   * @return string
   *   A string containing the redemption type.
   */
  public function getEgiftRedemptionTypeFromOrder(array $order) {
    $egiftRedeemType = '';
    $payment_info = '';
    // Proceed only if payment info is available.
    if (array_key_exists('payment_additional_info', $order['extension'])) {
      foreach ($order['extension']['payment_additional_info'] as $payment_item) {
        if ($payment_item['key'] == 'hps_redemption') {
          $payment_info = json_decode($payment_item['value'], TRUE);
          break;
        }
      }
      // Get the redemption type if payment info is available.
      if ($payment_info) {
        $egiftRedeemType = $payment_info['card_type'];
      }
    }

    return $egiftRedeemType;
  }

  /**
   * Helper to check if order item is having virtual items.
   *
   * @param array $order
   *   The order array.
   *
   * @return array
   *   An array containing the status of virtual items.
   */
  public function orderItemsVirtual(array $order) {
    // Return if items are missing
    if (!array_key_exists('items', $order)) {
      return [];
    }
    // Flag to keep track of egift and normal items.
    $allVirtualItems = TRUE;
    $normalItemsExists = FALSE;
    $virtualItemsExists = FALSE;
    $isTopup = FALSE;
    // Traverse all the items and check the product type.
    foreach($order['items'] as $key => $value) {
      if (!$value['is_virtual']) {
        $allVirtualItems = FALSE;
        $normalItemsExists = TRUE;
      } else {
        $virtualItemsExists = TRUE;
      }
    }
    // Check if order item is a topup item.
    if (array_key_exists('extension', $order)
      && array_key_exists('topup_card_number', $order['extension'])) {
      $isTopup = TRUE;
    }

    return [
      'allVirtualItems' => $allVirtualItems,
      'normalItemsExists' => $normalItemsExists,
      'virtualItemsExists' => $virtualItemsExists,
      'topUpItem' => $isTopup,
    ];
  }

  /**
   * Get egift landing page nid.
   *
   * @result null|int
   *   The eGift landing nid or null.
   **/
  public function egiftLandingPageNid() {
    // Set cache id for query result.
    $cid = 'egift_landing_page.advanced_page.nid';

    // Get data from cache.
    if ($cache = $this->cacheBackend->get($cid)) {
      return $cache->data;
    }

    // Query nids of type advanced_page and field use as landing page
    // is checked. Use the most recently created published node.
    $query = $this->entityTypeManager->getStorage('node')
      ->getQuery()
      ->condition('type', 'advanced_page')
      ->condition('field_use_as_egift_landing_page', TRUE)
      ->condition('status', NodeInterface::PUBLISHED)
      ->sort('created', 'DESC');
    $results = $query->execute();
    $egift_landing_page = reset($results);


    // Set query result with egift landing page nid in cache.
    // This cache tag is invalidated on queue when insert / update / delete
    // advanced_page node.
    if (!empty($egift_landing_page)) {
      $this->cacheBackend->set($cid, $egift_landing_page, Cache::PERMANENT, ['node_type:advanced_page']);
    }

    return $egift_landing_page;
  }

  /**
   * Update order details with egift related details.
   **/
  public function prepareOrderDetailsData(&$order, &$orderDetails) {
    // Do not proceed if Egift card is not enabled.
    if (!$this->isEgiftCardEnabled()) {
      return;
    }
    // Set order name if first item is virtual product.
    $item = reset($order['items']);
    if ($item['is_virtual']) {
      $orderDetails['#order']['name'] = $item['sku'] == 'giftcard_topup'
        ? t('eGift Card Top up', [], ['context' => 'egift'])
        : t('eGift Card', [], ['context' => 'egift']);
    }

    // Update Payment method name for hps payment.
    if ($orderDetails["#order_details"]["payment_method_code"] === 'hps_payment') {
      $orderDetails["#order_details"]["payment_method"] = t('eGift Card', [], ['context' => 'egift']);
      $egift_data = [
        'card_type' => t('eGift Card', [], ['context' => 'egift']),
        'card_number' => substr($order['extension']['hps_redemption_card_number'], -4),
        'payment_type' => 'egift',
        'weight' => -2,
      ];
      $orderDetails['#order_details']['paymentDetails']['egift'] = $egift_data;
      // Unset the `hps_payment` payment key if exists.
      if (isset($orderDetails['#order_details']['paymentDetails']['hps_payment'])) {
        unset($orderDetails['#order_details']['paymentDetails']['hps_payment']);
      }
    }

    // For multiple payment and if some amount is paid via egift then add eGift Card payment.
    if ($orderDetails["#order_details"]["payment_method_code"] !== 'hps_payment' && isset($order['extension']['hps_redeemed_amount']) && $order['extension']['hps_redeemed_amount'] > 0) {
      $orderDetails["#order_details"]["payment_method"] .= ', ' . t('eGift Card', [], ['context' => 'egift']);
      $egift_data = [
        'card_type' => t('eGift Card', [], ['context' => 'egift']),
        'card_number' => substr($order['extension']['hps_redemption_card_number'], -4),
        'payment_type' => 'egift',
        'weight' => -2,
      ];
      $orderDetails['#order_details']['paymentDetails']['egift'] = $egift_data;
    }
    // Update virtual product details.
    foreach ($orderDetails['#products'] as $key => &$product) {
      $style = $product['name'];

      // Show eGift card image and options from order details.
      if ($product['is_virtual']) {
        $product['name'] = $product['sku'] == 'giftcard_topup'
          ? t('eGift Card Top up', [], ['context' => 'egift'])
          : t('eGift Card', [], ['context' => 'egift']);

        // Get virtual product image.
        $product['image'] = [
          '#theme' => 'image',
          '#uri' => $product['extension_attributes']['product_media'][0]['file'],
          '#alt' => $product['name'],
        ];

        // Get product options and add them to attributes.
        $product_options = json_decode($product['extension_attributes']['product_options'][0], TRUE);
        $product['attributes'][0] = [
          'label' => t('Style', [], ['context' => 'egift']),
          'value' => $style,
        ];
        if (!empty($product_options['hps_giftcard_recipient_email'])) {
          $product['attributes'][1] = [
            'label' => t('Send to', [], ['context' => 'egift']),
            'value' => $product_options['hps_giftcard_recipient_email'],
          ];
        }
        if (!empty($product_options['hps_giftcard_message'])) {
          $product['attributes'][2] = [
            'label' => t('Message', [], ['context' => 'egift']),
            'value' => $product_options['hps_giftcard_message'],
          ];
        }
        if (!empty($product_options['hps_card_number'])) {
          $product['attributes'][3] = [
            'label' => t('Card No', [], ['context' => 'egift']),
            'value' => $product_options['hps_card_number'],
          ];
        }
      }
    }
  }

}
