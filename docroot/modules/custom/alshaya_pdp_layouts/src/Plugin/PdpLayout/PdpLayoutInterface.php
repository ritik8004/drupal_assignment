<?php

namespace Drupal\alshaya_pdp_layouts\Plugin\PdpLayout;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for PDP Layout plugins.
 */
interface PdpLayoutInterface extends PluginInspectionInterface {

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

  /**
   * Return the theme render array of the layout.
   *
   * @return array
   *   The theme render array of the pdp layout.
   */
  public function getRenderArray();

}
