<?php

namespace Drupal\acq_promotion\Plugin\AcqPromotion;

use Drupal\acq_promotion\AcqPromotionBase;
use Drupal\alshaya_acm\CartData;

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
  public function getActiveLabel(CartData $cart) {
    return $this->t('Your order qualifies for free delivery.');
  }

}
