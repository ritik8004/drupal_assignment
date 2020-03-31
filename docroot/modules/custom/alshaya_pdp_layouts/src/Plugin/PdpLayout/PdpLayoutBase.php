<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Base class for Pdp Layout plugins.
 */
abstract class PdpLayoutBase extends PluginBase implements PdpLayoutInterface, ContainerFactoryPluginInterface {

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
