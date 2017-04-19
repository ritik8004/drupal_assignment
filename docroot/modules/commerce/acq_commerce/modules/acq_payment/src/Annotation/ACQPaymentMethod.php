<?php

namespace Drupal\acq_payment\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a ACQ Checkout Pane item annotation object.
 *
 * @see \Drupal\acq_payment\CheckoutPaneManager
 * @see plugin_api
 *
 * @Annotation
 */
class ACQPaymentMethod extends Plugin {

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
