<?php

/**
 * @file
 * Contains \Drupal\acq_sku\AcquiaCommerce\Annotation\SKUType.
 */

namespace Drupal\acq_sku\AcquiaCommerce\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an SKU type annotation object.
 *
 * Plugin Namespace: Plugin\AcquiaCommerce/SKUType
 *
 * @see plugin_api
 * @see hook_sku_type_info_alter()
 *
 * @Annotation
 */
class SKUType extends Plugin {

  /**
   * The SKU type plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the SKU Type plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

  /**
   * The description of the SKU Type plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

}
