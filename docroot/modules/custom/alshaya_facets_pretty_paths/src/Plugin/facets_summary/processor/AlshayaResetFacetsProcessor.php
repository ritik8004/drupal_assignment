<?php

namespace Drupal\alshaya_facets_pretty_paths\Plugin\facets_summary\processor;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\facets_summary\FacetsSummaryInterface;
use Drupal\facets_summary\Plugin\facets_summary\processor\ResetFacetsProcessor;

/**
 * Provides a processor that allows to reset facet filters.
 *
 * @SummaryProcessor(
 *   id = "alshaya_reset_facets",
 *   label = @Translation("Adds reset facets link."),
 *   description = @Translation("When checked, this facet will add a link to reset enabled facets."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaResetFacetsProcessor extends ResetFacetsProcessor {

  /**
   * {@inheritdoc}
   */
  public function build(FacetsSummaryInterface $facets_summary, array $build, array $facets) {
    $conf = $facets_summary->getProcessorConfigs()[$this->getPluginId()];

    // Do nothing if there are no selected facets or reset text is empty.
    if (empty($build['#items']) || empty($conf['settings']['link_text'])) {
      return $build;
    }

    $request = \Drupal::requestStack()->getMasterRequest();
    $query_params = $request->query->all();
    unset($query_params['f']);

    // Remove all pretty facets.
    $uri = $request->getRequestUri();
    if (str_contains($uri, '/--')) {
      $uri = substr($uri, 0, strrpos($uri, '/--'));
    }
    $url = Url::fromUserInput($uri);
    $url->setOptions(['query' => $query_params]);

    // phpcs:ignore
    $item = (new Link($this->t($conf['settings']['link_text']), $url))->toRenderable();
    array_unshift($build['#items'], $item);
    return $build;
  }

}
