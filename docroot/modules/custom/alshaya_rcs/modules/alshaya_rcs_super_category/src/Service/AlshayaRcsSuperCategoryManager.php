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
    if (!$this->isEnabled()) {
      return NULL;
    }

    $default_category_tid = &drupal_static(__FUNCTION__);

    if (!isset($default_category_tid)) {
      $default_category_tid = 0;

      $status = $this->configFactory->get('alshaya_super_category.settings')->get('status');

      if ($status) {
        $super_categories_terms = $this->productCategoryTree->getCategoryRootTerms();

        if (!empty($super_categories_terms)) {
          $default_category_tid = current($super_categories_terms)['commerce_id'] ?? 0;
        }
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
    if (!$this->isEnabled()) {
      return NULL;
    }

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
