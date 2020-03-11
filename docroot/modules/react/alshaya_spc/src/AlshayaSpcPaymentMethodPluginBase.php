<?php

namespace Drupal\alshaya_spc;

use Drupal\Component\Plugin\PluginBase;

/**
 * Class AlshayaSpcPaymentMethodPluginBase.
 */
abstract class AlshayaSpcPaymentMethodPluginBase extends PluginBase {

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
