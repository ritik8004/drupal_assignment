<?php

namespace Drupal\alshaya_search\Plugin\facets_summary\processor;

use Drupal\facets_summary\FacetsSummaryInterface;
use Drupal\facets_summary\Processor\BuildProcessorInterface;
use Drupal\facets_summary\Processor\ProcessorPluginBase;

/**
 * Provides a processor that hides the facet when the facets were not rendered.
 *
 * @SummaryProcessor(
 *   id = "alshaya_strip_l2_params",
 *   label = @Translation("Hides additional query params set for facets in summary block."),
 *   description = @Translation("Hides additional query params set for facets in summary block."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaStripL2QueryParams extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetsSummaryInterface $facets_summary, array $build, array $facets) {
    // Do nothing if there are no selected facets.
    if (!isset($build['#items'])) {
      return $build;
    }

    if (!empty($build['#items'])) {
      foreach ($build['#items'] as $item) {
        $item_url = $item['#url'];
        $item_query_options = $item_url->getOption('query');
        if ($item_query_options) {
          unset($item_query_options['no_url_l2']);
          unset($item_query_options['current_facet']);
          $item_url->setOption('query', $item_query_options);
        }
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function isHidden() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return FALSE;
  }

}
