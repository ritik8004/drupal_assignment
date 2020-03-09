<?php

namespace Drupal\alshaya_search_api\Plugin\facets\processor;

use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Drupal\facets\Result\Result;

/**
 * Show `All` option in category facet.
 *
 * @FacetsProcessor(
 *   id = "alshaya_facet_all_option",
 *   label = @Translation("Alshaya Facet - All- option"),
 *   description = @Translation("Shows the -All- option in the facet."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class AlshayaFacetAllOption extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    // If there are results in the facet.
    if (!empty($results)) {
      // Get total count from all items in this facet.
      $max_items = array_sum(array_map(function ($item) {
        return $item->getCount();
      }, $results));

      // Prepare `All` result facet item.
      $all_result_item = new Result(0, $this->t('All'), $max_items);

      // Get any one result item from current facet, get its url and then use
      // this url for `all` result with some changes.
      foreach ($results as $result) {
        if ($result instanceof Result) {
          // Get the query parameters from url.
          $url = clone $result->getUrl();
          $query = $url->getOption('query');

          if (empty($query['f']) || !is_iterable($query['f'])) {
            continue;
          }

          // Remove the facet selections for current facet.
          foreach ($query['f'] as $key => $value) {
            if (strpos($value, $facet->getUrlAlias()) === 0) {
              unset($query['f'][$key]);
            }
          }

          $url->setOption('query', $query);
          $all_result_item->setUrl($url);

          // We need to process only for first real result.
          break;
        }
      }

      $results = array_merge([$all_result_item], $results);
    }

    // Return the results.
    return $results;
  }

}
