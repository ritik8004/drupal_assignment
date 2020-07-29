<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * KNET payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "knet",
 *   label = @Translation("K-NET"),
 *   hasForm = false
 * )
 */
class Knet extends AlshayaSpcPaymentMethodPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#strings']['knet_error'] = [
      'key' => 'knet_error',
      'value' => $this->t('Sorry, we are unable to process your payment. Please contact our customer service team for assistance.</br> Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code'),
    ];

    $build['#strings']['knet_error_info'] = [
      'key' => 'knet_error_info',
      'value' => $this->t('Order ID: @order_id Transaction ID: @transaction_id Payment ID: @payment_id Result code: @result_code'),
    ];
  }

}
