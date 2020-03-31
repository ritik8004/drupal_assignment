<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

/**
 * Defines an interface for PDP Layout plugins.
 */
interface PdpLayoutInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Return the id of the PDP Layout.
   *
   * @return string
   *   The id of the pdp layout.
   */
  public function getLayoutId();

  /**
   * Return the name of the PDP Layout.
   *
   * @return string
   *   The name of the pdp layout.
   */
  public function getLayoutName();

}
