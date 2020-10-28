<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#strings']['checkout_com_upapi_knet_error'] = [
      'key' => 'checkout_com_upapi_knet_error',
      'value' => $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.</br> Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code'),
    ];

    $build['#strings']['checkout_com_upapi_knet_error_info'] = [
      'key' => 'checkout_com_upapi_knet_error_info',
      'value' => $this->t('Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code'),
    ];
  }

}
