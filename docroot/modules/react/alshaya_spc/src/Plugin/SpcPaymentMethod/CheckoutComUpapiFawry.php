<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Fawry payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "checkout_com_upapi_fawry",
 *   label = @Translation("Fawry (Checkout.com)"),
 *   hasForm = false
 * )
 */
class CheckoutComUpapiFawry extends AlshayaSpcPaymentMethodPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $strings = [
      [
        'key' => 'fawry_payment_option_prefix_description',
        'value' => $this->t('You’ll receive your Fawry reference number on the contact details below once you’ve placed your order.​'),
      ],
      [
        'key' => 'fawry_payment_option_suffix_description',
        'value' => $this->t("Pay for your order through any of <a href='#'>Fawry's cash points</a> at your convenient time and location across Egypt."),
      ],
      [
        'key' => 'fawry_checkout_confirmation_message_prefix',
        'value' => $this->t('Cash payment with Fawry'),
      ],
      [
        'key' => 'fawry_checkout_confirmation_message',
        'value' => $this->t('Amount due - @amount. Please complete your payment at the nearest Fawry cash point using your reference #@reference_no by @date_and_time.​'),
      ],
    ];

    $build['#strings'] = array_merge($build['#strings'], $strings);
  }

}
