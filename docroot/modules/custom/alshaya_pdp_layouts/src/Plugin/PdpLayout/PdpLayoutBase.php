<?php

namespace Drupal\alshaya_pdp_layouts\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Pdp Layout plugins.
 */
abstract class PdpLayoutBase extends PluginBase implements PdpLayoutInterface {

  /**
   * {@inheritdoc}
   */
  public function getLayoutId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutName() {
    return $this->pluginDefinition['label'];
  }

}
