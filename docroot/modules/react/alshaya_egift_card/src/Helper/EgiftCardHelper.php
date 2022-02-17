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

}
