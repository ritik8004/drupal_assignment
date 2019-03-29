<?php

namespace Drupal\alshaya_acm_product_position;

use Drupal\views\Views;

/**
 * Class AlshayaPlpSortOptionsService.
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
    $config_values = $this->getCurrentPagePlpSortOptions();

    $config_sort_options = array_keys($config_values);
    // If there are at least any sort option enabled.
    if (!empty($config_sort_options)) {
      $new_sort_options = [];

      // Get the default sorting for options from views config.
      $views_storage = Views::getView('alshaya_product_list')->storage;
      $views_sort = $views_storage->getDisplay('block_1')['display_options']['sorts'];

      // Iterate over config sort options to prepare new sorted array for form
      // value option.
      foreach ($config_sort_options as $sort_options) {
        // Set default sort option ASC/DESC from the views config/sort order.
        $default_sort_order = $views_sort[$sort_options]['order'];
        $secondary_sort_order = $views_sort[$sort_options]['order'] == 'ASC' ? 'DESC' : 'ASC';
        if (isset($options[$sort_options . ' ' . $default_sort_order])) {
          $new_sort_options[$sort_options . ' ' . $default_sort_order] = $options[$sort_options . ' ' . $default_sort_order];
        }
        if (isset($options[$sort_options . ' ' . $secondary_sort_order])) {
          $new_sort_options[$sort_options . ' ' . $secondary_sort_order] = $options[$sort_options . ' ' . $secondary_sort_order];
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
  protected function getCurrentPagePlpSortOptions():array {
    if (($term = $this->getTermForRoute()) && $options = $this->getPlpSortConfigForTerm($term, 'options')) {
      return array_filter($options);
    }

    return array_filter($this->configSortOptions->get('sort_options'));
  }

}
