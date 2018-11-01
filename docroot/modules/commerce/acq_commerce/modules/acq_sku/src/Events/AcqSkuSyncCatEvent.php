<?php

namespace Drupal\acq_sku\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class AcqSkuSyncCatEvent.
 *
 * @package Drupal\acq_sku
 */
class AcqSkuSyncCatEvent extends Event {

  /**
   * Response data.
   *
   * @var array
   */
  protected $response = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $response_data = []) {
    $this->response = $response_data;
  }

  /**
   * Get response data.
   *
   * @return array
   *   Response data.
   */
  public function getResponseData() {
    return $this->response;
  }

  /**
   * Set response data.
   *
   * @param array $response_data
   *   Response data.
   *
   * @return $this
   *   Object.
   */
  public function setResponseData(array $response_data = []) {
    $this->response = $response_data;
    return $this;
  }

}
