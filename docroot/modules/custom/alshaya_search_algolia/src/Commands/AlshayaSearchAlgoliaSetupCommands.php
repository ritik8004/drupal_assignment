<?php

namespace Drupal\alshaya_search_algolia\Commands;

use Drupal\alshaya_algolia_react\Services\AlshayaAlgoliaReactConfig;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drush\Commands\DrushCommands;

/**
 * Class Alshaya Search Algolia Setup Commands.
 *
 * @package Drupal\alshaya_search_algolia\Commands
 */
class AlshayaSearchAlgoliaSetupCommands extends DrushCommands {

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * AlshayaSearchAlgoliaSetupCommands constructor.
   *
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   Facet manager.
   */
  public function __construct(DefaultFacetManager $facet_manager) {
    $this->facetManager = $facet_manager;
  }

  /**
   * Create facets.
   *
   * @command alshaya_search_algolia:get-facets
   *
   * @aliases alshaya-algolia-get-facets
   *
   * @usage drush alshaya-algolia-get-facets
   *   Create facets for the site.
   */
  public function getFacets() {
    $facets = $this->facetManager->getFacetsByFacetSourceId(AlshayaAlgoliaReactConfig::FACET_SOURCE);
    $facets_to_create = [
      'field_category_name',
      'lhn_category',
      'promotion_nid',
      // We need filterOnly SKU facet field in algolia product list index
      // for the wishlist page for sorting products list.
      'filterOnly(sku)',
    ];

    foreach ($facets as $facet) {
      $facets_to_create[$facet->getFieldIdentifier()] = $facet->getFieldIdentifier();
    }

    print_r(array_values($facets_to_create));
  }

}
