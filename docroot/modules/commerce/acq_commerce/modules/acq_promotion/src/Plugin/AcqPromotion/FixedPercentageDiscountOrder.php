<?php

namespace Drupal\acq_promotion\Plugin\AcqPromotion;

use Drupal\acq_promotion\AcqPromotionBase;

/**
 * Provides the Buy X Get Y Free cart level promotion.
 *
 * @ACQPromotion(
 *   id = "fixed_percentage_discount_order",
 *   label = @Translation("Get Y% discount on order over KWD X"),
 * )
 */
class FixedPercentageDiscountOrder extends AcqPromotionBase {

  /**
   * {@inheritdoc}
   */
  public function getActiveLabel() {
    return '';
  }

}
