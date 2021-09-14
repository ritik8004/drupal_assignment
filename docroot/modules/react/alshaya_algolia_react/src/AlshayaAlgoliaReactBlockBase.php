<?php

namespace Drupal\alshaya_algolia_react;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Base class to have the common code for Algolia Blocks.
 *
 * @package Drupal\alshaya_algolia_react
 */
abstract class AlshayaAlgoliaReactBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'languages',
      'route',
      'url',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'config:acq_commerce.currency',
      'config:alshaya_acm_product.display_settings',
      'config:alshaya_search_api.listing_settings',
      'config:alshaya_acm_product.settings',
      'config:alshaya_algolia_react.settings',
      'config:alshaya_algolia_react.product_frames',
    ]);
  }

}
