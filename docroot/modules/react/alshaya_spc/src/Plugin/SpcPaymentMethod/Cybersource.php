<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Checkout.com payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "cybersource",
 *   label = @Translation("Credit Card"),
 * )
 */
class Cybersource extends AlshayaSpcPaymentMethodPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#attached']['drupalSettings']['cybersource'] = [
      'acceptedCards' => Settings::get('cybersource')['accepted_cards'],
    ];

    $build['#attached']['library'][] = 'alshaya_white_label/secure-text';

    $build['#strings']['invalid_cybersource_card'] = [
      'key' => 'invalid_cybersource_card',
      'value' => $this->t('Invalid Credit Card number'),
    ];

    $build['#strings']['invalid_expiry'] = [
      'key' => 'invalid_expiry',
      'value' => $this->t('Incorrect credit card expiration date'),
    ];

    $build['#strings']['invalid_cvv'] = [
      'key' => 'invalid_cvv',
      'value' => $this->t('Invalid security code (CVV)'),
    ];
  }

}
