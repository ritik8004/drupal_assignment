<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\alshaya_config\AlshayaConfigManager;
use Drupal\alshaya_acm_product_category\ProductCategoryTreeInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Routing\RouteMatchInterface;

class AlshayaRcsSuperCategoryManager extends AlshayaSuperCategoryManager {

  /**
   * {@inheritdoc}
   */
  public function getDefaultCategoryId() {
    $default_category_tid = &drupal_static(__FUNCTION__);
    if (!isset($default_category_tid)) {
      $status = $this->configFactory->get('alshaya_super_category.settings')->get('status');
      if ($status) {
        $super_categories_terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('rcs_category',0, 1, TRUE);
        $default_category_tid = !empty($super_categories_terms) ? current($super_categories_terms)->id() : 0;
      }
      else {
        $default_category_tid = 0;
      }
    }

    return $default_category_tid;
  }
}
