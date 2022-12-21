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
    $build['#strings']['checkout_com_upapi_knet_error_info'] = [
      'key' => 'checkout_com_upapi_knet_error_info',
      'value' => $this->t('Transaction ID: @transaction_id  Payment ID: @payment_id Result code: @result_code Transaction Date: @transaction_date Transaction Time: @transaction_time'),
    ];
  }

}
