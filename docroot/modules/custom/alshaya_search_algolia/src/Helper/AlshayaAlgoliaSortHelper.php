<?php

namespace Drupal\alshaya_search_algolia\Helper;

use Drupal\alshaya_custom\AlshayaDynamicConfigValueBase;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;

/**
 * Class AlshayaAlgoliaSortHelper.
 *
 * Sort data helper.
 *
 * @package Drupal\alshaya_search_algolia\Helper
 */
class AlshayaAlgoliaSortHelper {

  /**
   * Get sort by list options to show.
   *
   * @param string $index_name
   *   The algolia index to use.
   * @param string $page_type
   *   Page Type.
   *
   * @return array
   *   The array of options with key and label.
   */
  public static function getSortByOptions($index_name, $page_type): array {
    if ($page_type === 'search') {
      $position = \Drupal::configFactory()->get('alshaya_search.settings');

      $enabled_sorts = array_filter($position->get('sort_options'), fn($item) => $item != '');

      $labels = AlshayaDynamicConfigValueBase::schemaArrayToKeyValue(
        (array) $position->get('sort_options_labels')
      );

    }
    else {
      $sort_options = \Drupal::service('alshaya_acm_product_position.sort_options');
      $enabled_sorts = $sort_options->getCurrentPagePlpSortOptions();

      // Remove un-supported sorting options.
      unset($enabled_sorts['stock_quantity']);

      $labels = \Drupal::service('alshaya_acm_product_position.sort_labels')
        ->getSortOptionsLabels();
      $labels = $sort_options->sortGivenOptions($labels);
    }
    $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $algolia_product_list_index = AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index');
    $sort_items = [];
    foreach ($labels as $label_key => $label_value) {
      if (empty($label_value)) {
        continue;
      }

      $sort_index_key = '';
      [$sort_key, $sort_order] = preg_split('/\s+/', $label_key);

      // We used different keys for listing and search.
      // For now till we completely migrate we will need to do workaround to map
      // them to match the search keys.
      $sort_key_mapping = [
        'name_1' => 'title',
        'nid' => 'search_api_relevance',
      ];

      $index_sort_key = $sort_key_mapping[$sort_key] ?? $sort_key;

      $gtm_sort_key = '';
      if ($index_sort_key == 'search_api_relevance') {
        $sort_index_key = $index_name;
        $gtm_sort_key = 'default';
      }
      elseif (in_array($sort_key, $enabled_sorts)) {
        $sort_index_key = $index_name . '_' . $index_sort_key . '_' . strtolower($sort_order);
        // Get index name by page type.
        if ($page_type === 'listing' && $algolia_product_list_index) {
          // Get sort index name for listing
          // eg: 01live_bbwae_product_list_en_title_asc.
          $sort_index_key = $index_name . '_'
            . $lang . '_'
            . $index_sort_key . '_'
            . strtolower($sort_order);
        }
        $gtm_sort_key = $index_sort_key . '_' . strtolower($sort_order);
      }

      if (!empty($sort_index_key)) {
        $sort_items[] = [
          'value' => $sort_index_key,
          'label' => $label_value,
          'gtm_key' => $gtm_sort_key,
        ];
      }

    }
    return $sort_items;
  }

  /**
   * Get Algolia Index Name.
   *
   * @param string $lang
   *   Attribute to identify language.
   * @param string $page_type
   *   Attribute to indentify page Type.
   *
   * @return string
   *   Index Name.
   */
  public static function getAlgoliaIndexName($lang, $page_type) {
    $index = \Drupal::configFactory()->get('search_api.index.alshaya_algolia_index')->get('options');
    // Set Algolia index name from Drupal index eg: 01live_bbwae_en.
    $index_name = $index['algolia_index_name'] . '_' . $lang;
    // Get current index name based on page type.
    if ($page_type === 'listing' && AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) {
      $index = \Drupal::configFactory()->get('search_api.index.alshaya_algolia_product_list_index')->get('options');
      // Set Algolia index name from Drupal index
      // eg: 01live_bbwae_product_list.
      $index_name = $index['algolia_index_name'];
    }
    return $index_name;
  }

}
