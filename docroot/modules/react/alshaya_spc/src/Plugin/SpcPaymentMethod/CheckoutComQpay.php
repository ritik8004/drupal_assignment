<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Qpay payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi_qpay",
 *   label = @Translation("Q-PAY"),
 *   hasForm = false
 * )
 */
class CheckoutComQpay extends AlshayaSpcPaymentMethodPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#strings']['checkout_com_upapi_qpay_error_info'] = [
      'key' => 'checkout_com_upapi_qpay_error_info',
      'value' => $this->t('Transaction ID: @transaction_id<br>Payment ID: @payment_id<br>Result code: @result_code<br>Amount: @amount<br>Date: @date'),
    ];
  }

}
