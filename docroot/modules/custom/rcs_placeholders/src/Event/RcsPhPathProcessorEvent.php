<?php

namespace Drupal\rcs_placeholders\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event for processing/altering RCS paths.
 */
class RcsPhPathProcessorEvent extends Event {
  public const EVENT_NAME = 'rcs_ph_path_processor_event';

  /**
   * Data for the event.
   *
   * @var array
   */
  private array $data = [
    'entityType' => NULL,
    'entityPath' => NULL,
    'entityPathPrefix' => NULL,
    'entityFullPath' => NULL,
    'processedPaths' => NULL,
    'entityData' => NULL,
  ];

  /**
   * Gets data.
   *
   * @return array
   *   Data value.
   */
  public function getData():array {
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

  /**
   * Add data to the data array.
   *
   * @param string $key
   *   Event data key.
   * @param mixed $value
   *   Value to set.
   */
  public function addData(string $key, mixed $value) {
    $this->data[$key] = $value;
  }

}
