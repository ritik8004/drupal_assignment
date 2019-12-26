<?php

namespace Drupal\acq_promotion;

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
   * @return mixed
   *   Inactive promo label.
   */
  public function getInactiveLabel();

  /**
   * Get active promo label.
   *
   * @return mixed
   *   Active promo label.
   */
  public function getActiveLabel();

  /**
   * Get promotion status based on cart.
   *
   * @return mixed
   *   Promotion status.
   */
  public function getPromotionCartStatus();

}
