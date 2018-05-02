<?php

namespace Drupal\alshaya_search\FacetManager;

use Drupal\facets\FacetManager\DefaultFacetManager;

/**
 * Override facet manager to allow  processing data posthierarchy building.
 */
class AlshayaSearchFacetManager extends DefaultFacetManager {

  /**
   * {@inheritdoc}
   */
  protected function buildHierarchicalTree(array $keyed_results, array $parent_groups) {
    $results = parent::buildHierarchicalTree($keyed_results, $parent_groups);
    \Drupal::moduleHandler()->alter('alshaya_facet_hierarchial_tree', $results);
    return $results;
  }

}
