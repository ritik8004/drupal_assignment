<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\alshaya_acm_product_position\AlshayaPlpSortOptionsService;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultSet;
use Drupal\views\Views;
use Drupal\facets\Result\Result;
use Drupal\facets\QueryType\QueryTypePluginManager;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\alshaya_acm_product_position\AlshayaPlpSortLabelsService;
use Drupal\alshaya_acm_product\Service\SkuPriceHelper;
use Drupal\alshaya_product_options\SwatchesHelper;
use Drupal\node\NodeInterface;
use Drupal\alshaya_search_algolia\Helper\AlshayaAlgoliaSortHelper;

/**
 * Class Alshaya Search Api Query Execute.
 */
class AlshayaSearchApiQueryExecute {

  use StringTranslationTrait;

  public const ALGOLIA_SORT_KEY_MAPPING = [
    'name_1' => 'title',
    'nid' => 'search_api_relevance',
  ];

  /**
   * Filter query string key.
   */
  public const FILTER_KEY = 'f';

  /**
   * Sort query string key.
   */
  public const SORT_KEY = 'sort';

  /**
   * Pager key.
   */
  public const PAGER_KEY = 'limit';

  /**
   * Page limit.
   *
   * Default pager limit when not provided.
   */
  public const PAGER_DEFAULT_LIMIT = 12;

  /**
   * Filter key and value separator.
   */
  public const SEPARATOR = ':';

  /**
   * Separator for the sort between field and sort order.
   */
  public const SORT_SEPARATOR = ' ';

  /**
   * Facet source id.
   *
   * @var string
   */
  protected $facetSourceId = 'search_api:views_block__alshaya_product_list__block_1';

  /**
   * Server index.
   *
   * @var string
   */
  protected $serverIndex = 'product';

  /**
   * Views id.
   *
   * @var string
   */
  protected $viewsId = 'alshaya_product_list';

  /**
   * Views display id.
   *
   * @var string
   */
  protected $viewsDisplayID = 'block_1';

  /**
   * Price facet key.
   *
   * @var string
   */
  protected $priceFacetKey = 'skus_sku_reference_final_price';

  /**
   * Price facet key.
   *
   * @var string
   */
  protected $sellingPriceFacetKey = 'plp_selling_price';

  /**
   * Processed facets array.
   *
   * @var array
   */
  protected $processedFacetsArray = [];

  /**
   * Price facet for special handling.
   *
   * @var null|\Drupal\facets\Entity\Facet
   */
  protected $priceFacet = NULL;

  /**
   * Total result count.
   *
   * @var int
   */
  protected $resultTotalCount = 0;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Facet manager.
   *
   * @var \Drupal\facets\FacetManager\DefaultFacetManager
   */
  protected $facetManager;

  /**
   * Query type plugin manager.
   *
   * @var \Drupal\facets\QueryType\QueryTypePluginManager
   */
  protected $queryTypePluginManager;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * SKU manager.
   *
   * @var \Drupal\alshaya_acm_product\SkuManager
   */
  protected $skuManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PLP sort option service.
   *
   * @var \Drupal\alshaya_acm_product_position\AlshayaPlpSortOptionsService
   */
  protected $plpSortOptions;

  /**
   * PLP sort labels service.
   *
   * @var \Drupal\alshaya_acm_product_position\AlshayaPlpSortLabelsService
   */
  protected $plpSortLabels;

  /**
   * SKU Price Helper.
   *
   * @var \Drupal\alshaya_acm_product\Service\SkuPriceHelper
   */
  private $priceHelper;

  /**
   * Swatch Helper service object.
   *
   * @var \Drupal\alshaya_product_options\SwatchesHelper
   */
  private $swatchesHelper;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Default Sort.
   *
   * @var string
   */
  protected $defaultSort = 'created DESC';

  /**
   * The facets to ignore in the output.
   *
   * @var array
   */
  protected $facetsToIgnore = [];

  /**
   * AlshayaSearchApiQueryExecute constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   Facet manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\facets\QueryType\QueryTypePluginManager $query_type_manager
   *   Query type plugin manager.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   Mobile app utility service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_acm_product\SkuManager $sku_manager
   *   SKU manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\alshaya_acm_product_position\AlshayaPlpSortOptionsService $sort_option_service
   *   Plp Sort options service.
   * @param \Drupal\alshaya_acm_product_position\AlshayaPlpSortLabelsService $sort_labels_service
   *   Plp Sort labels service.
   * @param \Drupal\alshaya_acm_product\Service\SkuPriceHelper $price_helper
   *   SKU Price Helper.
   * @param \Drupal\alshaya_product_options\SwatchesHelper $swatches_helper
   *   Swatches helper service object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(
    RequestStack $requestStack,
    DefaultFacetManager $facet_manager,
    LanguageManagerInterface $language_manager,
    QueryTypePluginManager $query_type_manager,
    MobileAppUtility $mobile_app_utility,
    EntityRepositoryInterface $entity_repository,
    SkuManager $sku_manager,
    EntityTypeManagerInterface $entity_type_manager,
    AlshayaPlpSortOptionsService $sort_option_service,
    AlshayaPlpSortLabelsService $sort_labels_service,
    SkuPriceHelper $price_helper,
    SwatchesHelper $swatches_helper,
    ConfigFactoryInterface $config_factory
  ) {
    $this->swatchesHelper = $swatches_helper;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->facetManager = $facet_manager;
    $this->languageManager = $language_manager;
    $this->queryTypePluginManager = $query_type_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->entityRepository = $entity_repository;
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->plpSortOptions = $sort_option_service;
    $this->plpSortLabels = $sort_labels_service;
    $this->priceHelper = $price_helper;
    $this->configFactory = $config_factory;
  }

  /**
   * Prepare and execute search api query.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   Search api query object.
   * @param string $page_type
   *   Page type.
   * @param string|null $keyword
   *   Search keyword.
   *
   * @return array
   *   Query results.
   */
  public function prepareExecuteQuery(QueryInterface $query, string $page_type, ?string $keyword = '') {
    // Get all facets for the given facet source.
    $facets = $this->facetManager->getFacetsByFacetSourceId($this->getFacetSourceId());
    // Get the processed facets array along with final price.
    $this->processedFacetsArray = $this->getProcessedFacets($facets);
    // Filter by published nodes as same is done in views.
    if ($query->getIndex()->id() !== 'alshaya_algolia_index') {
      $query->addCondition('status', NodeInterface::PUBLISHED);

      // Language filter.
      $query->setLanguages([$this->languageManager->getCurrentLanguage()->getId()]);
    }

    // Sort by the stock.
    $query->sort('stock', 'DESC');

    // Get query string parameters..
    $query_string_parameters = $this->currentRequest->query->all();

    // Query pagination.
    $page_offset = 0;
    $page_limit = self::PAGER_DEFAULT_LIMIT;
    // If pager info is available in query string.
    if (!empty($query_string_parameters[self::PAGER_KEY])) {
      // Get pager offset and limit info.
      $pager = explode(',', $query_string_parameters[self::PAGER_KEY]);
      $page_offset = (int) $pager[0];
      if (!empty($pager[1]) && is_int((int) $pager[1])) {
        $upper_limit = $this->getProductMaxLimit();
        $page_limit = ($upper_limit > $pager[1]) ? $pager[1] : $upper_limit;
      }
    }
    $query->range($page_offset, $page_limit);

    // Adding sort to the query.
    if (!empty($query_string_parameters[self::SORT_KEY])) {
      // Setting the default sort value.
      $this->defaultSort = $query_string_parameters[self::SORT_KEY];
      $sort_option = explode(self::SORT_SEPARATOR, $query_string_parameters[self::SORT_KEY]);

      // If both key and value available for sorting.
      if (!empty($sort_option[0]) && !empty($sort_option[1])) {
        // Use lower case only for field.
        $sort_option[0] = strtolower($sort_option[0]);

        if (!in_array(strtoupper($sort_option[1]), ['ASC', 'DESC'])) {
          // If not a valid sort order.
          $this->mobileAppUtility->throwException();
        }

        if ($query->getIndex()->getServerInstance()->id() === 'algolia') {
          $sort_option[0] = self::ALGOLIA_SORT_KEY_MAPPING[$sort_option[0]] ?? $sort_option[0];

          if ($sort_option[0] === 'search_api_relevance') {
            $sort_option[1] = 'DESC';
          }
        }

        // Get available sort options.
        $available_sort_data = $this->prepareSortData($this->getViewsId(), $this->getViewsDisplayId(), $page_type);
        $valid_key = FALSE;

        $sort_key = strtolower(implode(' ', $sort_option));
        foreach ($available_sort_data as $sort_data) {
          // If found a match for sort key.
          if (strtolower($sort_data['key']) === $sort_key) {
            $valid_key = TRUE;
            break;
          }
        }

        // If not a valid sort key.
        if (!$valid_key) {
          $this->mobileAppUtility->throwException();
        }

        $query->sort($sort_option[0], $sort_option[1]);
      }
      else {
        // If either sort key or sort value not available.
        $this->mobileAppUtility->throwException();
      }
    }
    // If no sort available, use default one.
    else {
      // If promo list page.
      if ($page_type === 'promo') {
        $default_sort = $this->getPromoDefaultSort();
        if ($query->getIndex()->getServerInstance()->id() === 'algolia') {
          $default_sort['key'] = self::ALGOLIA_SORT_KEY_MAPPING[$default_sort['key']] ?? $default_sort['key'];
        }

        $query->sort($default_sort['key'], $default_sort['order']);
      }
      elseif ($page_type === 'search') {
        // If search page, get default sort.
        $default_sort = $this->getSearchPageSortOptions(TRUE);
        $exploded_default_sort = explode(self::SORT_SEPARATOR, $default_sort);
        $query->sort($exploded_default_sort[0], $exploded_default_sort[1]);
      }
    }

    // If there are any filter/facet in query string available.
    if (!empty($query_string_parameters[self::FILTER_KEY])) {
      $filter_data = [];
      // Prepare filter/facets condition to the query.
      foreach ($query_string_parameters[self::FILTER_KEY] as $filter) {
        $filter_option = explode(self::SEPARATOR, $filter);
        // If filter key passed is valid facet key (available in facets).
        if (isset($this->processedFacetsArray[$filter_option[0]])) {
          // Storing facet data in array with key search api field machine name
          // to use it later for processing.
          $filter_data[$this->processedFacetsArray[$filter_option[0]]->getFieldIdentifier()][] = $filter_option[1];
        }
        else {
          // If filter key provided is not valid (not available in facets).
          $this->mobileAppUtility->throwException();
        }
      }

      // Adding filter/condition to the query.
      foreach ($filter_data as $filter_key => $filter_val) {
        if ($query->getIndex()->id() === 'alshaya_algolia_index') {
          if ($filter_key == 'field_acq_promotion_label') {
            $filter_key = 'field_acq_promotion_label.app';
          }
        }

        // In case of price facet, we need special/different handling.
        if ($filter_key == 'final_price') {
          $filter = $query->createConditionGroup('OR', ['facet:' . $filter_key]);
          foreach ($filter_val as $price) {
            $exclude = FALSE;
            /** @var \Drupal\alshaya_search\Plugin\facets\query_type\AlshayaSearchGranular $alshaya_search_granular */
            $alshaya_search_granular = $this->queryTypePluginManager->createInstance('alshaya_search_granular', [
              'facet' => $this->priceFacet,
              'query' => $query,
            ]);
            // Get the price range by facet price value.
            $range = $alshaya_search_granular->calculateRange($price);
            // Add to the condition.
            $price_filter = $query->createConditionGroup('AND', ['facet:' . $filter_key]);
            $price_filter->addCondition('final_price', $range['start'], $exclude ? '<' : '>');
            $price_filter->addCondition('final_price', $range['stop'], $exclude ? '>' : '<=');
            $filter->addConditionGroup($price_filter);
          }
          $query->addConditionGroup($filter);
        }
        else {
          $filter = $query->createConditionGroup('OR', ['facet:' . $filter_key]);
          foreach ($filter_val as $val) {
            $filter->addCondition($filter_key, $val);
          }
          $query->addConditionGroup($filter);
        }
      }
    }

    // Set additional options.
    // (In this case, retrieve facets, if supported by the backend.)
    $server = $query->getIndex()->getServerInstance();
    if ($server->supportsFeature('search_api_facets')) {
      $facet_data = [];
      foreach ($facets as $facet) {
        $identifier = $facet->getFieldIdentifier();
        // Need to change the facet identifier only if the index is
        // alshaya_algolia_index.
        if ($query->getIndex()->id() === 'alshaya_algolia_index') {
          if ($identifier == 'field_acq_promotion_label') {
            $identifier = 'field_acq_promotion_label.app';
          }
        }
        $facet_data[$facet->id()] = [
          'field' => $identifier,
          'limit' => $facet->getHardLimit(),
          'operator' => $facet->getQueryOperator(),
          'min_count' => $facet->getMinCount(),
          'missing' => FALSE,
        ];
      }

      $query->setOption('search_api_facets', $facet_data);
    }

    // Execute the search.
    if (empty($keyword) || strlen($keyword) >= $this->getMinSearchKeyCount()) {
      $results = $query->execute();
    }
    else {
      $results = new ResultSet($query);
    }

    // Process all facets in advance, instead of doing it on build.
    // We are updating facet results, in below foreach. So, we want to
    // make sure the facets are ready to receive updated results for current
    // query.
    $this->facetManager->processFacets($this->getFacetSourceId());
    // Set the result count.
    $this->setResultTotalCount($results->getResultCount());

    // Fill facets with the result data.
    $facet_build = [];

    $search_api_facets = $results->getExtraData('search_api_facets') ?? [];

    foreach ($facets as $facet) {
      // Show only one price facet - final_price or selling_price.
      if ($facet->id() == $this->getPriceFacetKey() && $this->priceHelper->isPriceModeFromTo()) {
        // Do not show final price if price mode from-to.
        continue;
      }
      elseif ($facet->id() == $this->getSellingPriceFacetKey() && !$this->priceHelper->isPriceModeFromTo()) {
        // Do not show selling price if price mode not from-to.
        continue;
      }

      $facet_result = [];
      if (isset($search_api_facets[$facet->id()])) {
        $facet_result = $search_api_facets[$facet->id()];
      }
      // For Algolia results come with field identifier.
      elseif (isset($search_api_facets[$facet->getFieldIdentifier()])) {
        $facet_result = $search_api_facets[$facet->getFieldIdentifier()];
      }
      elseif ($facet->getFieldIdentifier() === 'field_acq_promotion_label'
        && isset($search_api_facets['field_acq_promotion_label.app'])) {
        $facet_result = $search_api_facets['field_acq_promotion_label.app'];
      }

      $data = [];
      foreach ($facet_result ?? [] as $result) {
        // Prepare the result item object.
        $result['filter'] = trim($result['filter'], '"');
        $result['count'] = trim($result['count'], '"');
        $data[] = new Result($facet, $result['filter'], $result['filter'], $result['count']);
      }
      // Add the result item object to the facet.
      $facet->setResults($data);
      // Adding active value to the facet from url query string. Doing this as
      // we need the facet object in same state as on FE.
      if (!empty($filter_data[$facet->getFieldIdentifier()])) {
        $facet->setActiveItems($filter_data[$facet->getFieldIdentifier()]);
      }
      // Execute facet build so that facet processor gets executed.
      $facet_build[$facet->id()] = $this->facetManager->build($facet);
    }

    return [
      'facet_build' => $facet_build,
      'processed_facets' => $this->processedFacetsArray,
      'search_api_results' => $results,
    ];
  }

  /**
   * Get the Products max limit.
   */
  public function getProductMaxLimit() {
    $config = $this->configFactory->get('alshaya_mobile_app.settings');
    return $config->get('max_product_limit') ?? self::PAGER_DEFAULT_LIMIT;
  }

  /**
   * Get the Processed facets array.
   *
   * @param array $facets
   *   Actual facets.
   *
   * @return array
   *   Processed facets array.
   */
  protected function getProcessedFacets(array $facets) {
    // Prepare an array of key/value where key will be the facet id and value
    // will be the facet object.
    // Example - ['skus_sku_reference_final_price' => facet_object_here].
    foreach ($facets as $facet) {
      $this->processedFacetsArray[$facet->id()] = $facet;
      // Storing price facet temporary to use later for special handling.
      if ($facet->getFieldIdentifier() == 'final_price') {
        $this->priceFacet = $facet;
      }
    }
    return $this->processedFacetsArray;
  }

  /**
   * Prepare response data array.
   *
   * @param array $result_set
   *   Result set data.
   *
   * @return array
   *   Response array.
   */
  public function prepareResponseFromResult(array $result_set) {
    $respond_ignore_algolia_data = $this->configFactory->get('alshaya_mobile_app.settings')->get('listing_ignore_algolia_data');
    if ($respond_ignore_algolia_data) {
      // Get all facets for the given facet source.
      $facets = $this->facetManager->getEnabledFacets();
      // Get the processed facets array along with final price.
      $result_set['processed_facets'] = $this->getProcessedFacets($facets);
    }

    // Prepare facet data.
    $facet_result = $this->prepareFacetData($result_set);

    // Prepare product data.
    $product_data = isset($result_set['search_api_results']) ? $this->prepareProductData($result_set) : [];

    // Process the price facet for special handling.
    // Get price facet key.
    if ($this->priceHelper->isPriceModeFromTo()) {
      $price_facet_key = $this->getSellingPriceFacetKey();
    }
    else {
      $price_facet_key = $this->getPriceFacetKey();
    }
    foreach ($facet_result as &$facet) {
      // If price facet.
      if ($facet['key'] == $price_facet_key
        && !empty($result_set['search_api_results']) && isset($result_set['search_api_results']->getExtraData('search_api_facets')[$price_facet_key])
      ) {
        $facet = $this->processPriceFacet($result_set['search_api_results']->getExtraData('search_api_facets')[$price_facet_key], $result_set['search_api_results']->getQuery());
      }
    }

    // Sort the facet data.
    uasort($facet_result, [self::class, 'sort']);
    // Re-arrange keys.
    $facet_result = array_values($facet_result);
    // Removing weight key as now no longer required.
    foreach ($facet_result as &$fr) {
      unset($fr['weight']);
    }

    // Prepare final result.
    return [
      'filters' => $facet_result,
      'default_sort' => $this->defaultSort,
      'products' => $product_data,
      'total' => $this->getResultTotalCount(),
    ];
  }

  /**
   * Helper callback for uasort() to sort facets by weight and label.
   *
   * It is copy/paste of ConfigEntityBase::sort() which is used by
   * block/config system for sorting.
   */
  public static function sort(array $a, array $b) {
    $a_weight = $a['weight'] ?? 0;
    $b_weight = $b['weight'] ?? 0;
    if ($a_weight == $b_weight) {
      $a_label = $a['label'];
      $b_label = $b['label'];
      return strnatcasecmp($a_label, $b_label);
    }
    return ($a_weight < $b_weight) ? -1 : 1;
  }

  /**
   * Prepare facet results for response.
   *
   * @param array $result_set
   *   Result set array.
   *
   * @return array
   *   Facet data.
   */
  public function prepareFacetData(array $result_set) {
    $respond_ignore_algolia_data = $this->configFactory->get('alshaya_mobile_app.settings')->get('listing_ignore_algolia_data');
    $facets_data = $result_set['processed_facets'];

    // Prepare facet data first.
    $facet_result = [];
    foreach ($facets_data as $key => $facet) {
      // Do not further process facets that are not required.
      if (in_array($facet->id(), $this->getFacetsToIgnore())) {
        continue;
      }
      // Get facet block.
      $facet_block = $this->getFacetBlock($facet->id());
      // If facet block not available or not enabled, then skip it.
      if (empty($facet_block) || !$facet_block->status()) {
        continue;
      }
      // If no result available for a facet, skip that.
      $facet_results = $facet->getResults();
      // Respond all config labels when respond_algolia_data is false
      // If not process the available filters.
      if (empty($facet_results) && !$respond_ignore_algolia_data) {
        continue;
      }

      $facet_option_data = [];
      foreach ($facet_results as $result) {
        // For storing intermediate temporary data.
        if (strpos($key, 'color_family') > -1) {
          $swatch = $this->swatchesHelper->getSwatch('color_family', $result->getDisplayValue());
          $swatch_value = $swatch['name'] ?? $result->getDisplayValue();
          $result->setDisplayValue($swatch_value);
        }

        $temp_data = [
          'key' => $result->getRawValue(),
          'label' => $result->getDisplayValue(),
          'count' => $result->getCount(),
        ];

        // If children available, then add children to response.
        if (!empty($children = $result->getChildren())) {
          $i = 0;
          foreach ($children as $child) {
            $temp_data['children'][$i] = [
              'key' => $child->getRawValue(),
              'label' => $child->getDisplayValue(),
              'count' => $child->getCount(),
            ];
            // If L3 children available, then add them to response.
            if (!empty($l3_children = $child->getChildren())) {
              foreach ($l3_children as $l3_child) {
                $temp_data['children'][$i]['children'][] = [
                  'key' => $l3_child->getRawValue(),
                  'label' => $l3_child->getDisplayValue(),
                  'count' => $l3_child->getCount(),
                ];
              }
            }
            $i++;
          }
        }

        $facet_option_data[] = $temp_data;
      }
      $facet_result[] = [
        'key' => $key,
        'label' => $facet_block->label(),
        'weight' => $facet_block->getWeight(),
        'options' => $facet_option_data,
      ];
    }

    return $facet_result;
  }

  /**
   * Get facet block from facet id.
   *
   * @param string $facet_id
   *   Facet ID.
   *
   * @return null|\Drupal\block\Entity\Block
   *   Facet block object.
   */
  public function getFacetBlock(string $facet_id) {
    // Block id will be same as facet id with no underscore.
    // Example - plp_category_facet => plpcategoryfacet.
    $block_id = str_replace('_', '', $facet_id);
    // Load facet block to get title.
    $block = $this->entityTypeManager->getStorage('block')->load($block_id);
    if ($block instanceof Block) {
      return $block;
    }

    return NULL;
  }

  /**
   * Prepare response data array for products.
   *
   * @param array $result_set
   *   Result set data.
   *
   * @return array
   *   Response array.
   */
  public function prepareProductData(array $result_set) {
    $product_data = [];
    foreach ($result_set['search_api_results']->getResultItems() as $item) {
      // Parse the item id to fetch the node id. Normally item it in like
      // format - 'entity:node/1234:en' and we need to get 1234.
      $exploded_id = explode(':', $item->getId());
      $nid = explode('/', $exploded_id[1])[1];
      if ($nid) {
        $product_data[] = $this->mobileAppUtility->getLightProductFromNid($nid, $exploded_id[2]);
      }
    }

    return $product_data;
  }

  /**
   * Prepare response data array for sort.
   *
   * @param string $views_id
   *   Views machine id.
   * @param string $display_id
   *   Views display id.
   * @param string $page_type
   *   Page Type.
   *
   * @return array
   *   Sort array.
   */
  public function prepareSortData(string $views_id, string $display_id, string $page_type) {
    // If PLP views.
    $sort_data = [];
    $lang = $this->languageManager->getCurrentLanguage()->getId();
    $alshaya_algolia_index = AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_index');
    $algolia_product_list_index = AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index');
    if ($alshaya_algolia_index || $algolia_product_list_index) {
      $index_name = AlshayaAlgoliaSortHelper::getAlgoliaIndexName($lang, $page_type);
      $sort_data = AlshayaAlgoliaSortHelper::getSortByOptions($index_name, $page_type);
      return $sort_data;
    }

    // If plp/promo list page.
    if ($views_id == 'alshaya_product_list') {
      // If promo list page.
      if ($display_id == 'block_2') {
        $sort_data = $this->getPromoSortOptions($views_id);
      }
      else {
        // Get sort config.
        $sort_config = $this->plpSortLabels->getSortOptionsLabels();

        // Sorted sort data.
        $sort_config = $this->plpSortOptions->sortGivenOptions($sort_config);
        foreach ($sort_config as $key => $label) {
          $sort_data[] = [
            'key' => $key,
            'label' => $label,
          ];
        }
      }
    }
    else {
      // If search page.
      $sort_data = $this->getSearchPageSortOptions();
    }

    return $sort_data;
  }

  /**
   * Process the price facet result data.
   *
   * Here we processing the price facet to get the response/result of facet
   * same as we get on the FE. We getting facet value like 0.5, 5, 4.5 etc
   * individually and we need to group them all together to make a range like
   * we have on FE. Like '10 - 15' etc.
   *
   * @param array $price_facet_result
   *   Price facet result array.
   * @param object $query
   *   Search API Query object for the price facet.
   *
   * @return array
   *   Processed price facet result array.
   */
  public function processPriceFacet(array $price_facet_result, $query) {
    /** @var \Drupal\alshaya_search\Plugin\facets\query_type\AlshayaSearchGranular $alshaya_search_granular */
    $alshaya_search_granular = $this->queryTypePluginManager->createInstance('alshaya_search_granular', [
      'query' => $query,
      'facet' => $this->priceFacet,
      'results' => $price_facet_result,
    ]);

    // Get price facet build.
    /** @var \Drupal\facets\Entity\Facet $price_facet_build */
    $price_facet_build = $alshaya_search_granular->build();

    $option_data = [];
    $results = $price_facet_build->getResults();
    // Sort the price facet.
    ksort($results);
    foreach ($results as $result) {
      // Trim and remove html and newlines from the markup.
      $display_value = trim(str_replace([
        "\n",
        "\r",
      ], ' ', strip_tags($result->getDisplayValue())));
      // Remove extra spaces from text.
      $display_value = preg_replace('/\s\s+/', ' ', $display_value);
      $option_data[] = [
        'key' => (string) $result->getRawValue(),
        'label' => $display_value,
        'count' => $result->getCount(),
      ];
    }

    $facet_block = $this->getFacetBlock($price_facet_build->id());
    $price_facet_result = [
      'key' => $price_facet_build->id(),
      'label' => $facet_block ? $facet_block->label() : '',
      'weight' => $facet_block->getWeight(),
      'options' => $option_data,
    ];

    return $price_facet_result;
  }

  /**
   * Get the sort information of the search page.
   *
   * If we pass the default sort option, then this will return the first value
   * from the sorted sort options and that will be treated as default sort.
   *
   * @param bool $default_sort
   *   If we need the default sort option.
   *
   * @return array
   *   Sort options for search page page.
   */
  public function getSearchPageSortOptions(bool $default_sort = FALSE) {
    $sort_data = [];
    // Get sort config.
    $sort_config = _alshaya_search_get_config();
    $sort_config = ['search_api_relevance' => 'search_api_relevance'] + $sort_config;
    // Get labels of sort config.
    $sort_config_labels = _alshaya_search_get_config(TRUE);
    // Remove empty label items.
    $sort_config_labels = array_filter($sort_config_labels);

    // Get BEF sort settings from views.
    $views_storage = Views::getView('search')->storage;
    $bef_sort_settings = $views_storage->getDisplay('default')['display_options']['exposed_form']['options']['bef']['sort']['advanced']['combine_rewrite'];
    $lines = explode("\n", trim($bef_sort_settings));

    foreach ($sort_config as $key => $sort) {
      $asc_key = 0;
      $desc_key = 0;
      foreach ($lines as $line_key => $line_val) {
        if (isset($sort_config_labels[$key . ' ASC']) && str_contains($line_val, $sort_config_labels[$key . ' ASC'])) {
          $asc_key = $line_key;
        }
        if (isset($sort_config_labels[$key . ' DESC']) && str_contains($line_val, $sort_config_labels[$key . ' DESC'])) {
          $desc_key = $line_key;
        }
      }

      // Prepare sort order.
      $first_sort_order_key = $asc_key < $desc_key ? 'ASC' : 'DESC';
      $second_sort_order_key = $first_sort_order_key == 'ASC' ? 'DESC' : 'ASC';

      if (isset($sort_config_labels[$key . ' ' . $first_sort_order_key])) {
        $sort_data[] = [
          'key' => $key . ' ' . $first_sort_order_key,
          'label' => $sort_config_labels[$key . ' ' . $first_sort_order_key],
        ];
      }
      if (isset($sort_config_labels[$key . ' ' . $second_sort_order_key])) {
        $sort_data[] = [
          'key' => $key . ' ' . $second_sort_order_key,
          'label' => $sort_config_labels[$key . ' ' . $second_sort_order_key],
        ];
      }
    }

    // If we only need the default sort, return first value.
    if ($default_sort) {
      return $sort_data[0]['key'];
    }

    return $sort_data;
  }

  /**
   * Get the sort information of the promo list view.
   *
   * @param string $views_id
   *   View ID.
   *
   * @return array
   *   Sort options for promo list page.
   */
  public function getPromoSortOptions(string $views_id) {
    $sort_data = [];
    // Get enabled sort options.
    $enabled_sort_config = array_filter(_alshaya_acm_product_position_get_config());
    // Get and set sort order from the views config.
    $views_storage = Views::getView($views_id)->storage;
    $views_sort = $views_storage->getDisplay('default')['display_options']['sorts'];
    // Get enabled sort options from config.
    $enabled_sorts = _alshaya_acm_product_position_get_config(TRUE);
    foreach ($views_sort as $sort) {
      if ($sort['exposed'] && isset($enabled_sort_config[$sort['field']])) {
        $key = $sort['field'] . ' ' . $sort['order'];
        $reverse_order = $sort['order'] == 'ASC' ? 'DESC' : 'ASC';
        $reverse_order_key = $sort['field'] . ' ' . $reverse_order;
        $sort_data[] = [
          'key' => $key,
          'label' => $enabled_sorts[$key],
        ];
        $sort_data[] = [
          'key' => $reverse_order_key,
          'label' => $enabled_sorts[$reverse_order_key],
        ];
      }
    }

    return $sort_data;
  }

  /**
   * Get default sort applied on promo list view.
   *
   * @return array
   *   Default sort.
   */
  public function getPromoDefaultSort() {
    $views_storage = Views::getView('alshaya_product_list')->storage;
    $views_sort = $views_storage->getDisplay('default')['display_options']['sorts'];
    $enabled_sort_config = _alshaya_acm_product_position_get_config();

    $default_sort = [];

    foreach (array_filter($enabled_sort_config) as $sort) {
      if (isset($views_sort[$sort]) && $views_sort[$sort]['exposed']) {
        $default_sort = [
          'key' => $views_sort[$sort]['field'],
          'order' => $views_sort[$sort]['order'],
        ];

        $this->defaultSort = implode(' ', $default_sort);
        break;
      }
    }

    return $default_sort;
  }

  /**
   * Get facet source id.
   *
   * @return string
   *   Facet source id.
   */
  public function getFacetSourceId() {
    return $this->facetSourceId;
  }

  /**
   * Set the facet source string.
   *
   * @param string $facet_source_id
   *   Facet source id.
   *
   * @return $this
   *   Current object.
   */
  public function setFacetSourceId(string $facet_source_id) {
    $this->facetSourceId = $facet_source_id;
    return $this;
  }

  /**
   * Get the search api server index.
   *
   * @return string
   *   Search api server index.
   */
  public function getServerIndex() {
    return $this->serverIndex;
  }

  /**
   * Set the search api server index.
   *
   * @param string $server_index
   *   Query server index.
   *
   * @return $this
   *   Current object.
   */
  public function setServerIndex(string $server_index) {
    $this->serverIndex = $server_index;
    return $this;
  }

  /**
   * Get the views machine name.
   *
   * @return string
   *   views id.
   */
  public function getViewsId() {
    return $this->viewsId;
  }

  /**
   * Set the views id..
   *
   * @param string $views_id
   *   Views id.
   *
   * @return $this
   *   Current object.
   */
  public function setViewsId(string $views_id) {
    $this->viewsId = $views_id;
    return $this;
  }

  /**
   * Get the views display id.
   *
   * @return string
   *   Views display id.
   */
  public function getViewsDisplayId() {
    return $this->viewsDisplayID;
  }

  /**
   * Set the views display id.
   *
   * @param string $display_id
   *   Views display id.
   *
   * @return $this
   *   Current object.
   */
  public function setViewsDisplayId(string $display_id) {
    $this->viewsDisplayID = $display_id;
    return $this;
  }

  /**
   * Get price facet key.
   *
   * @return string
   *   Price facet key.
   */
  public function getPriceFacetKey() {
    return $this->priceFacetKey;
  }

  /**
   * Set price facet key.
   *
   * @param string $price_facet_key
   *   Price facet key.
   *
   * @return $this
   *   Current object.
   */
  public function setPriceFacetKey(string $price_facet_key) {
    $this->priceFacetKey = $price_facet_key;
    return $this;
  }

  /**
   * Get selling price facet key.
   *
   * @return string
   *   Selling price facet key.
   */
  public function getSellingPriceFacetKey() {
    return $this->sellingPriceFacetKey;
  }

  /**
   * Set selling price facet key.
   *
   * @param string $sellingPriceFacetKey
   *   Price facet key.
   */
  public function setSellingPriceFacetKey($sellingPriceFacetKey) {
    $this->sellingPriceFacetKey = $sellingPriceFacetKey;
  }

  /**
   * Get the total item count of query.
   *
   * @return int
   *   Total result items.
   */
  public function getResultTotalCount() {
    return $this->resultTotalCount;
  }

  /**
   * Set the total item count of query.
   *
   * @param int $count
   *   Total result count.
   *
   * @return int
   *   Current object.
   */
  public function setResultTotalCount(int $count) {
    $this->resultTotalCount = $count;
    return $this;
  }

  /**
   * Get minimum length for search keywords.
   *
   * @return int
   *   Minimum length.
   */
  private function getMinSearchKeyCount() {
    $config = $this->configFactory->get('views.view.search');
    return (int) ($config->get('display.default.display_options.filters.search_api_fulltext.min_length'));
  }

  /**
   * Get the facets which should be ignored in the output.
   *
   * @return array
   *   The array of facets names to ignore.
   */
  public function getFacetsToIgnore() {
    return $this->facetsToIgnore;
  }

  /**
   * Set which facets to be ignored in the output.
   */
  public function setFacetsToIgnore($facets) {
    $this->facetsToIgnore = $facets;
  }

}
