<?php

namespace Drupal\alshaya_rcs_super_category\Service;

use Drupal\alshaya_super_category\AlshayaSuperCategoryManager;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

class AlshayaRcsSuperCategoryManager extends AlshayaSuperCategoryManager {

  /**
   * {@inheritdoc}
   */
  public function getDefaultCategoryId() {
    $default_category_tid = &drupal_static(__FUNCTION__);
    if (!isset($default_category_tid)) {
      $default_category_tid = 0;

      $status = $this->configFactory->get('alshaya_super_category.settings')->get('status');

      if ($status) {
        $super_categories_terms = $this->productCategoryTree->getCategoryRootTerms();
        $default_category_tid = !empty($super_categories_terms)
          ? current($super_categories_terms)['commerce_id']
          : 0;
      }
    }

    return $default_category_tid;
  }

  /**
   * Get the Super Category Term for current page.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   Super Category Term if found.
   */
  public function getCategoryTermFromRoute(): ?TermInterface {
    static $term;

    if (isset($term)) {
      return $term;
    }

    $term = $this->productCategoryTree->getCategoryTermFromRoute();

    if (empty($term)) {
      $categories = $this->productCategoryTree->getCategoryRootTerms();
      if ($categories) {
        $category = reset($categories);
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($category['id']);
      }
    }

    return $term;
  }
}
