<?php

namespace Drupal\alshaya_acm_product_category\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired when processing categories data for categories API.
 */
class EnrichedCategoryDataAlterEvent extends Event {

  public const EVENT_NAME = 'enriched_category_data_alter';

  /**
   * The term data.
   *
   * @var array
   */
  protected $data;

  /**
   * Constructs the object.
   *
   * @param array $data
   *   The category data to alter.
   */
  public function __construct(array $data) {
    $this->data = $data;
  }

  /**
   * Get term data.
   *
   * @return array
   *   The term data.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Set term data.
   *
   * @param array $data
   *   The term data.
   */
  public function setData(array $data) {
    $this->data = $data;
  }

}
