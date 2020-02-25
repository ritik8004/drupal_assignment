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

      // Get active items for this facet.
      $active_item = $facet->getActiveItems();
      // If any active item in this facet.
      if ($active_item) {
        // Get any one result item from current facet, get its url and then use
        // this url for `all` result with some changes.
        $one_result = end($results);
        if ($one_result instanceof Result) {
          // Get the query parameters from url.
          $url = clone $one_result->getUrl();
          $query = $url->getOption('query');

          if ($query) {
            $options = $query['f'];
            // Checks for non-empty value to be used in loop.
            if (!empty($options) && is_iterable($options)) {
              foreach ($options as $key => $option) {
                // Unset all active items of this facet to prepare url for the
                // `All` facet item.
                unset($options[$key]);
              }
            }

            $query['f'] = $options;
            $url->setOption('query', $query);
            $all_result_item->setUrl($url);
          }
        }
      }

      $results = [0 => $all_result_item] + $results;
    }

    // Return the results.
    return $results;
  }

}
