<?php

namespace Drupal\acq_promotion\Plugin\AcqPromotion;

use Drupal\acq_promotion\AcqPromotionBase;

/**
 * Provides the Buy X Get Y Free cart level promotion.
 *
 * @ACQPromotion(
 *   id = "free_shipping_order",
 *   label = @Translation("Free shipping with an order over X KD"),
 *   status = TRUE,
 * )
 */
class FreeShippingOrder extends AcqPromotionBase {

  /**
   * {@inheritdoc}
   */
  public function getActiveLabel() {
    return $this->t('Your order qualifies for free delivery.');
  }

}
