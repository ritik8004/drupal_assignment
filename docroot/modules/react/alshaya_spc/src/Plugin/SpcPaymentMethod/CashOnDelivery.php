<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;

/**
 * COD payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "cashondelivery",
 *   label = @Translation("Cash on Delivery"),
 *   hasForm = false
 * )
 */
class CashOnDelivery extends AlshayaSpcPaymentMethodPluginBase {

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#strings'] = array_merge($build['#strings'], self::getCodSurchargeStrings());

    // Get COD payment method mobile verification settings.
    $cod_mobile_verification = self::getCodMobileVerificationSettings();
    if ($cod_mobile_verification) {
      $build['#attached']['drupalSettings']['codMobileVerification'] = $cod_mobile_verification;
      $build['#attached']['library'][] = 'alshaya_white_label/checkout-cod-mobile-verification';
    }
  }

  /**
   * Strings related to COD.
   *
   * @return array
   *   Translated strings array.
   */
  public static function getCodSurchargeStrings() {
    $strings = [];

    $checkout_settings = \Drupal::config('alshaya_acm_checkout.settings');

    $string_keys = [
      'cod_surcharge_label',
      'cod_surcharge_description',
      'cod_surcharge_short_description',
      'cod_surcharge_tooltip',
    ];

    foreach ($string_keys as $key) {
      $strings[] = [
        'key' => $key,
        'value' => trim(preg_replace("/[\r\n]+/", '', $checkout_settings->get($key))),
      ];
    }

    return $strings;
  }

  /**
   * Get COD payment method settings for mobile verification.
   *
   * @return array|mixed|null
   *   Configuration value.
   */
  public static function getCodMobileVerificationSettings() {
    return \Drupal::config('alshaya_spc.settings')->get('cod_mobile_verification');
  }

}
