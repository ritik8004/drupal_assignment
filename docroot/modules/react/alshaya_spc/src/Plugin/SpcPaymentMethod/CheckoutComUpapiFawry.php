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
    $build['#strings'] = array_merge($build['#strings'], self::getFawryStaticText());
  }

  /**
   * Strings related to Fawry payment-method.
   *
   * @return array
   *   Translated strings array.
   */
  public static function getFawryStaticText() {
    return [
      [
        'key' => 'fawry_payment_option_prefix_description',
        'value' => t('Once the product is successfully requested, we will send you Fawry Ref. No. to the below contact details'),
      ],
      [
        'key' => 'fawry_payment_option_suffix_description',
        'value' => t("Pay for your order through any of <a href='@fawry_link' target='_blank'>Fawry's cash points</a> at your convenient time and location across Egypt.",
          ['@fawry_link' => 'https://fawry.com/storelocator']
        ),
      ],
      [
        'key' => 'fawry_checkout_confirmation_message_prefix',
        'value' => t('Cash payment with Fawry'),
      ],
      [
        'key' => 'fawry_checkout_confirmation_message',
        'value' => t('Amount due - @amount. Please complete your payment at the nearest Fawry cash point using your reference #@reference_no by @date_and_time.​'),
      ],
    ];
  }

}
