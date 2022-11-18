<?php

namespace Drupal\rcs_placeholders;

/**
 * Event for processing/altering RCS paths.
 */
class RcsPhPathProcessorEvent {
  public const EVENT_NAME = 'rcs_ph_path_processor_event';

  /**
   * Data for the event.
   *
   * @var array
   */
  private $data = [];

  /**
   * Gets data.
   *
   * @return array
   *   Data value.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Sets data.
   *
   * @param array $value
   *   Value to set.
   */
  public function setData(array $value) {
    $this->data = $value;
  }

}
