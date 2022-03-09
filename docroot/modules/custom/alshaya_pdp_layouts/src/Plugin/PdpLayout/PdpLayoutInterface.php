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

  /**
   * Return the name of the PDP Layout template.
   *
   * @return string
   *   The name of the pdp layout template.
   */
  public function getTemplateName(array &$suggestions, string $bundle);

  /**
   * Return the render array of the PDP Layout.
   *
   * @return array
   *   The render array of the pdp layout.
   */
  public function getRenderArray(array &$variables);

  /**
   * Return the context key of the PDP Layout.
   *
   * @return string
   *   The context key of the PDP Layout.
   */
  public function getCotextFromPdpLayout(string $context, string $pdp_layout);

}
