<?php

namespace Drupal\alshaya_seo_transac\Controller;

use Drupal\alshaya_main_menu\ProductCategoryTree;
use Drupal\Core\Controller\ControllerBase;

/**
 * Class AlshayaSeoController.
 */
class AlshayaSeoController extends ControllerBase {

  /**
   * Controller for the site map.
   */
  public function siteMap() {
    $data = [];
    if ($this->moduleHandler()->moduleExists('alshaya_main_menu')) {
      $data = \Drupal::service('alshaya_main_menu.product_category_tree')->getCategoryTreeCached();
    }

    $build = [
      '#theme' => 'alshaya_sitemap',
      '#term_tree' => $data,
    ];

    // Discard cache for the page once a term gets updated.
    $build['#cache']['tags'][] = ProductCategoryTree::CACHE_TAG;

    return $build;
  }

}
