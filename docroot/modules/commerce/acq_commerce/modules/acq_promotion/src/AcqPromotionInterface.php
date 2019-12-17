<?php

namespace Drupal\acq_promotion;

/**
 * An interface for all Acq Promotion type plugins.
 */
interface AcqPromotionInterface {

  /**
   * Get inactive promo label.
   *
   * @param bool $thresholdReached
   *   Cart Total reached promotion threshold or not.
   *
   * @return mixed
   *   Inactive promo label.
   */
  public function getInactiveLabel($thresholdReached = FALSE);

  /**
   * Get active promo label.
   *
   * @return mixed
   *   Active promo label.
   */
  public function getActiveLabel();

}
