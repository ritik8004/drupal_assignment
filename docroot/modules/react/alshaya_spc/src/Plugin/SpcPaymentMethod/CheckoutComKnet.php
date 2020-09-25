<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;

/**
 * Knet (Checkout.com) payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi_knet",
 *   label = @Translation("K-NET"),
 *   hasForm = false
 * )
 */
class CheckoutComKnet extends AlshayaSpcPaymentMethodPluginBase {
}
