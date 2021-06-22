<?php

namespace Drupal\alshaya_spc\Plugin\SpcPaymentMethod;

use Drupal\alshaya_spc\AlshayaSpcPaymentMethodPluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * BenefitPay payment method for SPC.
 *
 * @AlshayaSpcPaymentMethod(
 *   id = "benefitpay",
 *   label = @Translation("Benefit Pay"),
 *   hasForm = false
 * )
 */
class BenefitPay extends AlshayaSpcPaymentMethodPluginBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function processBuild(array &$build) {
    $build['#strings'] = array_merge($build['#strings'], self::getBenefitPayStaticText());
  }

  /**
   * Strings related to benefit-pay payment-method.
   *
   * @return array
   *   Translated strings array.
   */
  public static function getBenefitPayStaticText() {
    // @todo Update array with Benefit pay strings and their keys.
    return [
      'key' => '',
      'value' => '',
    ];
  }

}
