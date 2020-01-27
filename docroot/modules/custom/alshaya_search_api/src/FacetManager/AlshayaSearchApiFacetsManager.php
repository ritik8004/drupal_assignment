<?php

namespace Drupal\alshaya_search_api\FacetManager;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\facets\FacetSource\FacetSourcePluginManager;
use Drupal\facets\Processor\ProcessorPluginManager;
use Drupal\facets\QueryType\QueryTypePluginManager;
use Drupal\facets\Widget\WidgetPluginManager;

/**
 * The facet manager.
 *
 * The manager is responsible for interactions with the Search backend, such as
 * altering the query, it is also responsible for executing and building the
 * facet. It is also responsible for running the processors.
 *
 * This overrides Drupal\facets\FacetManager\DefaultFacetManager.
 */
class AlshayaSearchApiFacetsManager extends DefaultFacetManager {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(QueryTypePluginManager $query_type_plugin_manager,
                              WidgetPluginManager $widget_plugin_manager,
                              FacetSourcePluginManager $facet_source_manager,
                              ProcessorPluginManager $processor_plugin_manager,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($query_type_plugin_manager, $widget_plugin_manager, $facet_source_manager, $processor_plugin_manager, $entity_type_manager);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabledFacets() {
    $facets = parent::getEnabledFacets();

    // Prepare block ids.
    foreach ($facets as $facet) {
      $block_id = str_replace('_', '', $facet->id());
      $mapping[$block_id] = $facet->id();
    }

    if (!empty($mapping)) {
      $block_ids = $this->entityTypeManager->getStorage('block')->getQuery()
        ->condition('id', array_keys($mapping), 'IN')
        ->condition('status', 1)
        ->sort('weight', 'ASC')
        ->execute();

      $sorted = [];
      $weight = 1;
      foreach ($block_ids as $block_id) {
        $sorted[$mapping[$block_id]] = $weight++;
      }

      // Remove the facets for which block isn't displayed.
      foreach ($facets as $key => $facet) {
        if (!isset($sorted[$key])) {
          unset($facets[$key]);
        }
      }

      // Sort the facets by block weight.
      uasort($facets, function ($a, $b) use ($sorted) {
        return ($sorted[$a->id()] < $sorted[$b->id()]) ? -1 : 1;
      });
    }

    return $facets;
  }

}
