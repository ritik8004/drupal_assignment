<?php

namespace Drupal\alshaya_spc;

use Drupal\Component\Plugin\PluginBase;

/**
 * Class AlshayaSpcPaymentMethodPluginBase.
 */
abstract class AlshayaSpcPaymentMethodPluginBase extends PluginBase {

  /**
   * Add additional JS / CSS libraries for the plugin.
   *
   * @param array $build
   *   Build array from controller.
   */
  public function addAdditionalLibraries(array &$build) {
    // Add required libraries.
  }

}
