<?php

namespace Drupal\acq_sku\Entity\Controller;

use Drupal\Core\Entity\EntityViewBuilder;

/**
 * SKU View Builder class.
 */
class SKUViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function build(array $build) {
    $build = parent::build($build);

    $sku = $build['#acq_sku'];
    $plugin_manager = \Drupal::service('plugin.manager.sku');
    $plugin_definition = $plugin_manager->pluginFromSKU($sku);

    if (empty($plugin_definition)) {
      return $build;
    }

    $class = $plugin_definition['class'];
    $plugin = new $class();

    // Allow blocking of add to cart render.
    if (!isset($build['#no_add_to_cart']) || !($build['#no_add_to_cart'])) {
      /*
       * @TODO: remove custom form_builder once
       *   https://www.drupal.org/node/766146 is fixed.
       */
      $build['add_to_cart'] = \Drupal::service('acq_sku.form_builder')->getForm($plugin, $sku);
      $build['add_to_cart']['#weight'] = 50;
    }

    $build = $plugin->build($build);

    return $build;
  }

}
