<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;

/**
 * Qpay payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi_qpay",
 *   label = @Translation("Qpay (Checkout.com)"),
 *   hasForm = false
 * )
 */
class CheckoutComQpay extends AlshayaSpcPaymentMethodPluginBase {
}
