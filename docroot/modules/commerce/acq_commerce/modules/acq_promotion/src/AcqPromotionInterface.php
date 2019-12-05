<?php

namespace Drupal\acq_promotion;

/**
 * An interface for all Acq Promotion type plugins.
 */
interface AcqPromotionInterface {

  const ACQ_PROMOTION_DEFAULT_PRIORITY = 0;

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

}
