<?php

namespace Drupal\acq_checkout\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a ACQ Checkout Pane item annotation object.
 *
 * @see \Drupal\acq_checkout\CheckoutFlowManager
 * @see plugin_api
 *
 * @Annotation
 */
class ACQCheckoutFlow extends Plugin {

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
