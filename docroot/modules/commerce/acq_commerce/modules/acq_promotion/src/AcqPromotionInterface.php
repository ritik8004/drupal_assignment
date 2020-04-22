<?php

namespace Drupal\acq_promotion;

use Drupal\alshaya_acm\CartData;

/**
 * An interface for all Acq Promotion type plugins.
 */
interface AcqPromotionInterface {

  /**
   * Promotion is eligible to be applied on cart.
   */
  const STATUS_CAN_BE_APPLIED = 1;

  /**
   * Get inactive promo label.
   *
   * @param \Drupal\alshaya_acm\CartData $cart
   *   Cart Data.
   *
   * @return mixed
   *   Inactive promo label.
   */
  public function getInactiveLabel(CartData $cart);

  /**
   * Get active promo label.
   *
   * @param \Drupal\alshaya_acm\CartData $cart
   *   Cart Data.
   *
   * @return mixed
   *   Active promo label.
   */
  public function getActiveLabel(CartData $cart);

  /**
   * Get promotion status based on cart.
   *
   * @param \Drupal\alshaya_acm\CartData $cart
   *   Cart Data.
   *
   * @return bool
   *   Promotion status.
   */
  public function getPromotionCartStatus(CartData $cart);

  /**
   * Get promotion code label for cart promotions.
   *
   * @param \Drupal\alshaya_acm\CartData $cart
   *   Cart Data.
   *
   * @return string
   *   Label.
   */
  public function getPromotionCodeLabel(CartData $cart);

}
