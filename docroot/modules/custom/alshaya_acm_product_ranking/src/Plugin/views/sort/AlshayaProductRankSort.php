<?php

namespace Drupal\alshaya_acm_product_ranking\Plugin\views\sort;

use Drupal\search_api\Plugin\views\sort\SearchApiSort;

/**
 * Sort handler for product by rank.
 *
 * @ViewsSort("alshaya_product_rank_sort")
 */
class AlshayaProductRankSort extends SearchApiSort {

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
    // in alshaya_acm_product_ranking_search_api_db_query_alter().
  }

}
