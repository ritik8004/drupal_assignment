<?php

namespace Drupal\alshaya_egift_card\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
   * EgiftCardHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory service object.
   * @param \Drupal\token\TokenInterface $token
   *   Token interface.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TokenInterface $token
  ) {
    $this->configFactory = $config_factory;
    $this->token = $token;
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
    if (array_key_exists('hps_redeemed_amount', $order['extension'])) {
      return TRUE;
    }

    return FALSE;
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
        $allVirtualItems = TRUE;
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

}
