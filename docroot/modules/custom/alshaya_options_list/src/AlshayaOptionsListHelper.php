<?php

namespace Drupal\alshaya_options_list;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Link;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\file\Entity\File;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\acq_sku\SKUFieldsManager;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\search_api\Entity\Index;

/**
 * Helper functions for alshaya_options_list.
 */
class AlshayaOptionsListHelper {

  /**
   * Options page cache tag.
   */
  const OPTIONS_PAGE_CACHETAG = 'alshaya-options-page';

  /**
   * Database connection service object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;


  /**
   * File storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * SKU Fields Manager.
   *
   * @var \Drupal\acq_sku\SKUFieldsManager
   */
  protected $skuFieldsManager;

  /**
   * Parse mode plugin manager.
   *
   * @var \Drupal\search_api\ParseMode\ParseModePluginManager
   */
  protected $parseModeManager;

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * AlshayaOptionsListHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\acq_sku\SKUFieldsManager $sku_fields_manager
   *   SKU Fields Manager.
   * @param \Drupal\search_api\ParseMode\ParseModePluginManager $parse_mode_manager
   *   Parse mode plugin manager.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   Facet manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(Connection $connection,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              SKUFieldsManager $sku_fields_manager,
                              ParseModePluginManager $parse_mode_manager,
                              DefaultFacetManager $facet_manager) {
    $this->connection = $connection;
    $this->languageManager = $language_manager;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->configFactory = $config_factory;
    $this->skuFieldsManager = $sku_fields_manager;
    $this->parseModeManager = $parse_mode_manager;
    $this->facetManager = $facet_manager;
  }

  /**
   * Returns the build for options page.
   *
   * @param string $attributeCode
   *   Attribute code.
   * @param array $facet_results
   *   Array containing all facet results.
   * @param bool $showImages
   *   Whether images should be shown with the attribute.
   * @param bool $group
   *   Whether the attribute should be grouped alphabetically or not.
   * @param string $searchString
   *   Search string to match with name.
   *
   * @return array
   *   All term names array.
   */
  public function fetchAllTermsForAttribute($attributeCode, array $facet_results, $showImages = FALSE, $group = FALSE, $searchString = '') {
    $return = [];

    // If there are no results for attribute.
    if (empty($facet_results[$attributeCode])) {
      return [];
    }
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['name', 'tid']);
    $query->innerJoin('taxonomy_term__field_sku_attribute_code', 'tfa', 'tfd.tid = tfa.entity_id');
    $query->condition('tfa.field_sku_attribute_code_value', $attributeCode);
    $query->condition('tfd.langcode', $langcode);
    if ($showImages) {
      $query->addField('tfs', 'field_options_list_bg_target_id', 'image');
      $query->leftJoin('taxonomy_term__field_options_list_bg', 'tfs', 'tfa.entity_id = tfs.entity_id');
    }
    if ($group) {
      $query->orderBy('tfd.name');
    }
    if (!empty($searchString)) {
      $query->condition('tfd.name', '%' . $this->connection->escapeLike($searchString) . '%', 'LIKE');
    }
    $options = $query->execute()->fetchAllAssoc('tid');

    if (empty($options)) {
      return $return;
    }

    foreach ($options as $option) {
      if (!empty($option->name) && in_array($option->name, $facet_results[$attributeCode])) {
        $list_array = [];
        $list_array['title'] = $option->name;
        $list_array['url'] = $this->getAttributeUrl($attributeCode, $option->name);
        if ($showImages) {
          if (!empty($option->image)) {
            $file = $this->fileStorage->load($option->image);
            if ($file instanceof File) {
              $list_array['image_url'] = $file->getFileUri();
            }
          }
        }
        $return[] = $list_array;
      }
    }
    return $return;
  }

  /**
   * Group attributes starting with the same alphabet.
   *
   * @param array $options_array
   *   List of all options.
   *
   * @return array
   *   Alphabetically grouped array.
   */
  public function groupAlphabetically(array $options_array) {
    $return_array = [];
    foreach ($options_array as $option) {
      $char = strtolower($option['title'][0]);
      $return_array[$char][] = $option;
    }
    return $return_array;
  }

  /**
   * Get all facet attribute.
   *
   * @return array
   *   All attributes that have facets enabled.
   */
  public function getAttributeCodeOptions() {
    $query = $this->connection->select('taxonomy_term__field_sku_attribute_code', 'tfa');
    $query->fields('tfa', ['field_sku_attribute_code_value']);
    $query->groupBy('tfa.field_sku_attribute_code_value');
    $options = $query->execute()->fetchAllKeyed(0, 0);
    $filtered_options = [];
    // Only show those fields which have a facet.
    $fields = $this->skuFieldsManager->getFieldAdditions();
    foreach ($options as $key => $option) {
      if ($fields[$option]['facet'] == 1) {
        $filtered_options[$key] = $fields[$option]['label'] ?? $option;
      }
    }
    return $filtered_options;
  }

  /**
   * Check if alshaya options page feature is enabled.
   *
   * @return bool
   *   TRUE, if enabled. FALSE, if not.
   */
  public function optionsPageEnabled() {
    $config = $this->configFactory->get('alshaya_options_list.settings');
    return $config->get('alshaya_shop_by_pages_enable') ? TRUE : FALSE;
  }

  /**
   * Return links of all options pages that have been created.
   *
   * @return array
   *   The links array.
   */
  public function getOptionsPagesLinks() {
    static $links;

    if (!isset($links)) {
      $links = [];
      $pages = $this->configFactory->get('alshaya_options_list.settings')
        ->get('alshaya_options_pages');
      if (!empty($pages)) {
        foreach ($pages as $page) {
          $route_name = 'alshaya_options_list.options_page' . str_replace('/', '-', $page['url']);
          if (isset($page['menu-title'])) {
            $links[] = Link::createFromRoute($page['menu-title'], $route_name, []);
          }
        }
      }
    }
    return $links;
  }

  /**
   * Return all required facet results.
   *
   * @param array $attribute_codes
   *   List of all attributes that are selected.
   *
   * @return array
   *   Array of all facets data.
   *
   * @throws \Drupal\search_api\SearchApiException
   */
  public function loadFacetsData(array $attribute_codes) {
    $facet_results = &drupal_static('allRequiredFacetResults', []);
    if (!empty($facet_results)) {
      return $facet_results;
    }

    $facets = $this->facetManager->getFacetsByFacetSourceId('search_api:views_page__search__page');
    $index = Index::load('acquia_search_index');
    $query = $index->query();
    // Change the parse mode for the search.
    $parse_mode = $this->parseModeManager->createInstance('terms');
    $parse_mode->setConjunction('OR');
    $query->setParseMode($parse_mode);
    $query->setLanguages([
      $this->languageManager->getCurrentLanguage()
        ->getId(),
    ]);
    $query->keys('');
    $query->setFulltextFields($index->getFulltextFields());
    $server = $index->getServerInstance();
    if ($server->supportsFeature('search_api_facets')) {
      $facet_data = [];
      foreach ($facets as $facet) {
        if (in_array($facet->id(), $attribute_codes)) {
          $facet_data[$facet->id()] = [
            'field' => $facet->getFieldIdentifier(),
            'operator' => $facet->getQueryOperator(),
            'limit' => $facet->getHardLimit(),
            'min_count' => $facet->getMinCount(),
            'missing' => FALSE,
          ];
        }
      }
      $query->setOption('search_api_facets', $facet_data);
    }

    // Execute the search.
    $results = $query->execute();

    // Set the facet results data in static.
    $raw_facet_results = $results->getExtraData('search_api_facets');

    foreach ($raw_facet_results as $attribute_code => $results) {
      foreach ($results as $filter) {
        $facet_results[$attribute_code][] = trim($filter['filter'], '"');
      }
    }

    return $facet_results;
  }

  /**
   * Return links of all options pages that have been created.
   *
   * @param string $attributeCode
   *   Attribute code.
   * @param string $value
   *   Value for the facet.
   *
   * @return \Drupal\Core\Url
   *   The url for the attribute.
   *
   * @todo When DLP is enabled on search, add condition to generate pretty url.
   */
  public function getAttributeUrl($attributeCode, $value) {
    $url_options = [
      'query' => [
        'f[0]' => $attributeCode . ':' . $value,
      ],
    ];
    return Url::fromUri('internal:/search', $url_options);
  }

}
