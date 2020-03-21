<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\Site\Settings;

/**
 * Checkout.com payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "cybersource",
 *   label = @Translation("Credit Card"),
 * )
 */
class Cybersource extends AlshayaSpcPaymentMethodPluginBase {

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#attached']['drupalSettings']['cybersource'] = [
      'acceptedCards' => Settings::get('cybersource')['accepted_cards'],
    ];

    $build['#attached']['library'][] = 'alshaya_white_label/secure-text';
  }

}
