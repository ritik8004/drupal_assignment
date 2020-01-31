<?php

namespace Drupal\alshaya_search_algolia\Commands;

use Drupal\alshaya_algolia_react\Plugin\Block\AlshayaAlgoliaReactAutocomplete;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drush\Commands\DrushCommands;

/**
 * Class AlshayaSearchAlgoliaSetupCommands.
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
    $facets = $this->facetManager->getFacetsByFacetSourceId(AlshayaAlgoliaReactAutocomplete::FACET_SOURCE);
    $facets_to_create = [];
    foreach ($facets as $facet) {
      $facets_to_create[$facet->getFieldIdentifier()] = $facet->getFieldIdentifier();
    }

    print_r($facets_to_create);
  }

}
