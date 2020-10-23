<?php

namespace Drupal\alshaya_spc;

use Drupal\Component\Plugin\PluginBase;

/**
 * Class Alshaya Spc Payment Method Plugin Base.
 */
abstract class AlshayaSpcPaymentMethodPluginBase extends PluginBase {

  /**
   * Allow checking if a particular payment method is available or not.
   *
   * @return bool
   *   TRUE (default) if available.
   */
  public function isAvailable() {
    return TRUE;
  }

  /**
   * Add additional JS / CSS / drupalSettings for the plugin.
   *
   * @param array $build
   *   Build array from controller.
   */
  public function processBuild(array &$build) {
    // Add required libraries.
  }

}
