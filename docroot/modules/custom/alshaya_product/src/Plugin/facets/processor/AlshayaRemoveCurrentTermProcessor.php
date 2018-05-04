<?php

namespace Drupal\alshaya_product\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;

/**
 * Removes other terms (except child terms of current term) from facet items.
 *
 * @FacetsProcessor(
 *   id = "alshaya_remove_current_term",
 *   label = @Translation("Alshaya remove the current term"),
 *   description = @Translation("Removes the current term from the facet items (Only for PLP)."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaRemoveCurrentTermProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    if (!empty($results)) {
      // For PLP page, the result url will always point to current plp page, so
      // we assuming the first result will give the current plp page tid.
      $current_term = $results[0]->getUrl()->getRouteParameters()['taxonomy_term'];

      // If no term, means no PLP page. So we not process further.
      if (empty($current_term)) {
        return $results;
      }

      // Children of current term.
      $children = $facet->getHierarchyInstance()->getNestedChildIds($current_term);

      foreach ($results as $key => $result) {
        // If term is not the child of current term.
        if (!in_array($result->getRawValue(), $children)) {
          unset($results[$key]);
        }
      }
    }

    // Return the results.
    return $results;
  }

}
