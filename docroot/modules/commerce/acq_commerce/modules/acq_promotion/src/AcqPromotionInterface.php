<?php

namespace Drupal\acq_promotion;

/**
 * An interface for all Acq Promotion type plugins.
 */
interface AcqPromotionInterface {

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
