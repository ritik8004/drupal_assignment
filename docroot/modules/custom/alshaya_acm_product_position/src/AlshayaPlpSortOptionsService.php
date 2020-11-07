<?php

namespace Drupal\alshaya_acm_product_position;

use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\taxonomy\TermInterface;

/**
 * Class Alshaya Plp Sort Options Service.
 */
class AlshayaPlpSortOptionsService extends AlshayaPlpSortOptionsBase {

  /**
   * Sort the given options.
   *
   * @param array $options
   *   Array of plp options to sort.
   *
   * @return array
   *   Sorted array with labels.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function sortGivenOptions(array $options): array {
    if ($config_sort_options = array_keys($this->getCurrentPagePlpSortOptions())) {
      // If there are at least any sort option enabled.
      $new_sort_options = [];
      // Iterate over config sort options to prepare new sorted array for form
      // value option.
      foreach ($config_sort_options as $sort_options) {
        // Set labels for sort option ASC/DESC.
        foreach (['DESC', 'ASC'] as $sort_order) {
          if (isset($options[$sort_options . ' ' . $sort_order])) {
            $new_sort_options[$sort_options . ' ' . $sort_order] = $options[$sort_options . ' ' . $sort_order];
          }
        }
      }

      if (!empty($new_sort_options)) {
        return $new_sort_options;
      }
    }

    return $options;
  }

  /**
   * Get sort options for current page.
   *
   * @return array|null
   *   Return array of plp sort options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getCurrentPagePlpSortOptions():array {
    static $options;

    if (!empty($options)) {
      return $options;
    }

    $term = $this->getTermForRoute();
    if ($term instanceof TermInterface) {
      $options = $this->getPlpSortConfigForTerm($term, 'options');
    }

    // Fallback to config.
    if (empty($options)) {
      $options = $this->configSortOptions->get('sort_options');
    }

    $options = array_filter($options);

    if (!AlshayaSearchApiHelper::isIndexEnabled('product')) {
      unset($options['stock_quantity']);
    }

    return $options;
  }

}
