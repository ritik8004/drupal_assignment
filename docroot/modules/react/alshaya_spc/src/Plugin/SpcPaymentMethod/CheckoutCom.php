<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;

/**
 * Checkout.com payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com",
 *   label = @Translation("Credit / Debit Card"),
 * )
 */
class CheckoutCom extends AlshayaSpcPaymentMethodPluginBase {

  /**
   * {@inheritdoc}
   */
  public function addAdditionalLibraries(array &$build) {
    // @TODO: Add configuration for this and use live for live.
    $build['libraries'][] = 'alshaya_spc/checkout_sandbox_kit';

    // @TODO: Add libraries based on user type to handle saved cards separately.
    $build['libraries'][] = 'alshaya_spc/checkout_com';
  }

}
