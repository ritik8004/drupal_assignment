<?php

namespace Drupal\alshaya_seo\Controller;

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
      $data = \Drupal::service('alshaya_main_menu.product_category_tree')->getCategoryTree();
    }
    $build = [
      '#theme' => 'alshaya_sitemap',
      '#term_tree' => $data,
    ];

    return $build;
  }

}
