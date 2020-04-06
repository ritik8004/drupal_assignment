<?php

namespace Drupal\acq_promotion\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class PromotionMappingUpdatedEvent.
 *
 * @package Drupal\acq_promotion\Event
 */
class PromotionMappingUpdatedEvent extends Event {

  const EVENT_NAME = 'acq_promotion.promotion_mapping_updated';

  /**
   * SKUs.
   *
   * @var array
   */
  protected $skus;

  /**
   * PromotionMappingUpdatedEvent constructor.
   *
   * @param array $skus
   *   SKUs.
   */
  public function __construct(array $skus) {
    $this->skus = $skus;
  }

  /**
   * Get SKUs.
   *
   * @return array
   *   SKUs.
   */
  public function getSkus() {
    return $this->skus;
  }

}
