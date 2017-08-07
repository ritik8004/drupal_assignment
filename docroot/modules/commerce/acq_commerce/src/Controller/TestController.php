<?php

namespace Drupal\acq_commerce\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * TestController class.
 */
class TestController extends ControllerBase {

  /**
   * Callback for acq_commerce.test route.
   */
  public function testConnection() {
    return [
      '#markup' => print_r(\Drupal::service('acq_commerce.api')->systemWatchdog(), TRUE),
    ];
  }

}
