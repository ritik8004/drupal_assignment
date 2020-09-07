<?php

namespace Drupal\alshaya_pdp_layouts\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Pdp Layout item annotation object.
 *
 * @see \Drupal\alshaya_pdp_layouts\PdpLayoutManager
 * @see plugin_api
 *
 * @Annotation
 */
class PdpLayout extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
