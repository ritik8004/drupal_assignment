<?php

namespace Drupal\alshaya_acm_product_position\Plugin\views\sort;

use Drupal\search_api\Plugin\views\sort\SearchApiSort;

/**
 * Sort handler for product by position.
 *
 * @ViewsSort("alshaya_product_position_sort")
 */
class AlshayaProductPositionSort extends SearchApiSort {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Just copy/paste from parent.
    if (isset($this->query->orderby)) {
      unset($this->query->orderby);
      $sort = &$this->query->getSort();
      $sort = [];
    }

    // Not doing anything here. Not setting any sort as we are adding sort query
    // in alshaya_acm_product_position_search_api_db_query_alter().
  }

}
