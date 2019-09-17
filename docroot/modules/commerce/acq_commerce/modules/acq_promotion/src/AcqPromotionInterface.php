<?php

namespace Drupal\acq_promotion;

/**
 * An interface for all Acq Promotion type plugins.
 */
interface AcqPromotionInterface {

  const ACQ_PROMOTION_DEFAULT_PRIORITY = 0;

  /**
   * Get promotion priority.
   *
   * @return mixed
   *   Promotion priority.
   */
  public function getPriority();

  /**
   * Get inactive promo label display threshold order value.
   *
   * @return mixed
   *   Threshold order value.
   */
  public function getInactiveLabelThreshold();

  /**
   * Get active promo label display threshold order value.
   *
   * @return mixed
   *   Threshold order value.
   */
  public function getActiveLabelThreshold();

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
