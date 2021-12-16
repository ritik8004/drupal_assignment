<?php

namespace Drupal\alshaya_egift_card\Helper;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
   * The api helper object.
   *
   * @var Drupal\alshaya_egift_card\Helper\EgiftCardHelper
   */

    /**
     * EgiftCardHelper constructor.
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   Config Factory service object.
     */
  public function __construct(
    ConfigFactoryInterface $config_factory
  ) {
    $this->configFactory = $config_factory;
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
      ? $config->get('topup_terms_conditions_text')['value']
      : '';

    return [
      '#markup' => '<p>' . $this->t('Terms & Conditions', [], ['context' => 'egift']) . '</p>' . $term_conditions_text,
    ];
  }

}
