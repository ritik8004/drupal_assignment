<?php

namespace Drupal\acq_promotion\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a ACQ Promotion Type item annotation object.
 *
 * @see \Drupal\acq_promotion\AcqPromotionPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class ACQPromotion extends Plugin {
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

  /**
   * Status flag for promotion type.
   *
   * @var bool
   */
  public $status;

}
