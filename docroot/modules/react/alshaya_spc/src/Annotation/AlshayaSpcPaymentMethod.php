<?php

namespace Drupal\alshaya_spc\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines annotation object for SPC payment method.
 *
 * @see \Drupal\alshaya_spc\AlshayaSpcPaymentMethodManager
 * @see plugin_api
 *
 * @Annotation
 */
class AlshayaSpcPaymentMethod extends Plugin {

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
