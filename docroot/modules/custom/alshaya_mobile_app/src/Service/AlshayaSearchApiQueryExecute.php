<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\acq_sku\Entity\SKU;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\Node;
use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alshaya_acm_product\SkuManager;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Entity\Index;
use Drupal\views\Views;
use Drupal\facets\Result\Result;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\facets\QueryType\QueryTypePluginManager;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AlshayaSearchApiQueryExecute.
 */
class AlshayaSearchApiQueryExecute {

  use StringTranslationTrait;

  /**
   * Filter query string key.
   */
  const FILTER_KEY = 'f';

  /**
   * Sort query string key.
   */
  const SORT_KEY = 'sort';

  /**
   * Pager key.
   */
  const PAGER_KEY = 'limit';

  /**
   * Page limit.
   *
   * Default pager limit when not provided.
   */
  const PAGER_DEFAULT_LIMIT = 12;

  /**
   * Filter key and value separator.
   */
  const SEPARATOR = ':';

  /**
   * Separator for the sort between field and sort order.
   */
  const SORT_SEPARATOR = ' ';

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
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(RequestStack $requestStack,
                              DefaultFacetManager $facet_manager,
                              LanguageManagerInterface $language_manager,
                              QueryTypePluginManager $query_type_manager,
                              MobileAppUtility $mobile_app_utility,
                              EntityRepositoryInterface $entity_repository,
                              SkuManager $sku_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ModuleHandlerInterface $module_handler) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->facetManager = $facet_manager;
    $this->languageManager = $language_manager;
    $this->queryTypePluginManager = $query_type_manager;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->entityRepository = $entity_repository;
    $this->skuManager = $sku_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Prepare and execute search api query.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   Search api query object.
   *
   * @return array
   *   Query results.
   */
  public function prepareExecuteQuery(QueryInterface $query) {
    // Get all facets for the given facet source.
    $facets = $this->facetManager->getFacetsByFacetSourceId($this->getFacetSourceId());

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

    // Language filter.
    $query->setLanguages([$this->languageManager->getCurrentLanguage()->getId()]);
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
        $page_limit = $pager[1];
      }
    }
    $query->range($page_offset, $page_limit);

    // Adding sort to the query.
    if (!empty($query_string_parameters[self::SORT_KEY])) {
      $sort_option = explode(self::SORT_SEPARATOR, $query_string_parameters[self::SORT_KEY]);
      // If both key and value available for sorting.
      if (!empty($sort_option[0]) && !empty($sort_option[1])) {
        if (!in_array(strtoupper($sort_option[1]), ['ASC', 'DESC'])) {
          // If not a valid sort order.
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
      if ($this->getViewsId() == 'alshaya_product_list' && $this->getViewsDisplayId() == 'block_2') {
        $default_sort = $this->getPromoDefaultSort();
        $query->sort($default_sort['key'], $default_sort['order']);
      }
      elseif ($this->getViewsId() == 'search' && $this->getViewsDisplayId() == 'page') {
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
      }

      // Adding filter/condition to the query.
      foreach ($filter_data as $filter_key => $filter_val) {
        // In case of price facet, we need special/different handling.
        if ($filter_key == 'final_price') {
          $filter = $query->createConditionGroup('OR', ['facet:' . $filter_key]);
          foreach ($filter_val as $price) {
            $exclude = FALSE;
            /* @var \Drupal\alshaya_search\Plugin\facets\query_type\AlshayaSearchGranular $alshaya_search_granular */
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
    $server = Index::load($this->getServerIndex())->getServerInstance();
    if ($server->supportsFeature('search_api_facets')) {
      $facet_data = [];
      foreach ($facets as $facet) {
        $facet_data[$facet->id()] = [
          'field' => $facet->getFieldIdentifier(),
          'limit' => $facet->getHardLimit(),
          'operator' => $facet->getQueryOperator(),
          'min_count' => $facet->getMinCount(),
          'missing' => FALSE,
        ];
      }

      $query->setOption('search_api_facets', $facet_data);
    }

    // Execute the search.
    $results = $query->execute();

    // Set the result count.
    $this->setResultTotalCount($results->getResultCount());

    // Fill facets with the result data.
    $facet_build = [];
    foreach ($facets as $facet) {
      $facet_result = $results->getExtraData('search_api_facets')[$facet->id()];
      $data = [];
      foreach ($facet_result as $result) {
        // Prepare the result item object.
        $result['filter'] = trim($result['filter'], '"');
        $result['count'] = trim($result['count'], '"');
        $data[] = new Result($result['filter'], $result['filter'], $result['count']);
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
   * Prepare response data array.
   *
   * @param array $result_set
   *   Result set data.
   *
   * @return array
   *   Response array.
   */
  public function prepareResponseFromResult(array $result_set) {
    // Prepare facet data.
    $facet_result = $this->prepareFacetData($result_set);

    // Prepare product data.
    $product_data = $this->prepareProductData($result_set);

    // Process the price facet for special handling.
    foreach ($facet_result as &$facet) {
      // If price facet.
      if ($facet['key'] == $this->getPriceFacetKey()) {
        $facet = $this->processPriceFacet($result_set['search_api_results']->getExtraData('search_api_facets')[$this->getPriceFacetKey()]);
      }
    }

    // Prepare sort data.
    $sort_data = $this->prepareSortData($this->getViewsId(), $this->getViewsDisplayId());

    // Prepare final result.
    return [
      'filters' => $facet_result,
      'sort' => $sort_data,
      'products' => $product_data,
      'total' => $this->getResultTotalCount(),
    ];
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
    $facets_data = $result_set['processed_facets'];
    // Prepare facet data first.
    $facet_result = [];
    foreach ($facets_data as $key => $facet) {
      // If facet is not enabled, then skip it.
      if (!$this->isFacetEnabled($facet->id())) {
        continue;
      }
      // If no result available for a facet, skip that.
      $facet_results = $facet->getResults();
      if (empty($facet_results)) {
        continue;
      }

      $facet_option_data = [];
      foreach ($facet_results as $result) {
        // For storing intermediate temporary data.
        $temp_data = [
          'key' => $result->getRawValue(),
          'label' => $result->getDisplayValue(),
          'count' => $result->getCount(),
        ];

        // If children available, then add children to response.
        if (!empty($children = $result->getChildren())) {
          foreach ($children as $child) {
            $temp_data['children'][] = [
              'key' => $child->getRawValue(),
              'label' => $child->getDisplayValue(),
              'count' => $child->getCount(),
            ];
          }
        }

        $facet_option_data[] = $temp_data;
      }
      $facet_result[] = [
        'key' => $key,
        'label' => $this->getFacetBlockTitle($facet->id()),
        'options' => $facet_option_data,
      ];
    }

    return $facet_result;
  }

  /**
   * Get facet block title from facet id.
   *
   * @param string $facet_id
   *   Facet ID.
   *
   * @return string
   *   Facet block title.
   */
  public function getFacetBlockTitle(string $facet_id) {
    // Block id will be same as facet id with no underscore.
    // Example - plp_category_facet => plpcategoryfacet.
    $block_id = str_replace('_', '', $facet_id);
    // Load facet block to get title.
    $block = $this->entityTypeManager->getStorage('block')->load($block_id);
    if ($block instanceof Block) {
      return $block->label();
    }

    return '';
  }

  /**
   * Checks if facet enabled or not on FE.
   *
   * @param string $facet_id
   *   Facet ID.
   *
   * @return bool
   *   True if facet enabled.
   */
  public function isFacetEnabled(string $facet_id) {
    $block_id = str_replace('_', '', $facet_id);
    // Load facet block to get title.
    $block = $this->entityTypeManager->getStorage('block')->load($block_id);
    if ($block instanceof Block) {
      return $block->status();
    }

    return FALSE;
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
      $product_data[] = $this->getLightProduct($nid, $exploded_id[2]);
    }

    return $product_data;
  }

  /**
   * Get light product data.
   *
   * @param int $nid
   *   Node id.
   * @param string $langcode
   *   Language of node.
   *
   * @return array
   *   Product data.
   */
  public function getLightProduct(int $nid, string $langcode = 'en') {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    // If node exists in system.
    if ($node instanceof Node) {
      // Get translated node.
      $node = $this->entityRepository->getTranslationFromContext($node, $langcode);
      // Get SKU attached with node.
      $sku = $node->get('field_skus')->getString();
      $sku_entity = SKU::loadFromSku($sku);
      // If SKU exists in system.
      if ($sku_entity instanceof SKU) {
        // Get the prices.
        $prices = $this->skuManager->getMinPrices($sku_entity);

        // Get the promotion data.
        $promotions = $this->mobileAppUtility->getPromotions($sku_entity);
        // Get promo labels.
        $promo_label = $this->skuManager->getDiscountedPriceMarkup($prices['price'], $prices['final_price']);
        if ($promo_label) {
          $promotions[] = [
            'text' => $promo_label,
          ];
        }

        // Get label for the SKU.
        $labels = $this->mobileAppUtility->getLabels($sku_entity, 'plp');

        // Get media (images/video) for the SKU.
        $images = $this->mobileAppUtility->getMedia($sku_entity, 'search');

        $data = [
          'id' => $node->id(),
          'title' => $node->getTitle(),
          'original_price' => $prices['price'],
          'final_price' => $prices['final_price'],
          'promo' => $promotions,
          'images' => $images,
          'labels' => $labels,
        ];

        // Allow other modules to alter light product data.
        $this->moduleHandler->alter('alshaya_mobile_app_light_product_data', $sku_entity, $data);

        return $data;
      }
    }

    return [];
  }

  /**
   * Prepare response data array for sort.
   *
   * @param string $views_id
   *   Views machine id.
   * @param string $display_id
   *   Views display id.
   *
   * @return array
   *   Sort array.
   */
  public function prepareSortData(string $views_id, string $display_id) {
    // If PLP views.
    $sort_data = [];
    // If plp/promo list page.
    if ($views_id == 'alshaya_product_list') {
      // If promo list page.
      if ($display_id == 'block_2') {
        $sort_data = $this->getPromoSortOptions();
      }
      else {
        // Get sort config.
        $sort_config = _alshaya_acm_product_position_get_config(TRUE);
        // Remove empty label items.
        $sort_config = array_filter($sort_config);

        // Sorted sort data.
        $sort_config = _alshaya_acm_product_position_sorted_options($sort_config);
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
   *
   * @return array
   *   Processed price facet result array.
   */
  public function processPriceFacet(array $price_facet_result) {
    /* @var \Drupal\alshaya_search\Plugin\facets\query_type\AlshayaSearchGranular $alshaya_search_granular */
    $alshaya_search_granular = $this->queryTypePluginManager->createInstance('alshaya_search_granular', [
      'facet' => $this->priceFacet,
      'results' => $price_facet_result,
    ]);

    // Get price facet build.
    /* @var \Drupal\facets\Entity\Facet $price_facet_build */
    $price_facet_build = $alshaya_search_granular->build();

    $option_data = [];
    $results = $price_facet_build->getResults();
    // Sort the price facet.
    ksort($results);
    foreach ($results as $result) {
      // Trim and remove html and newlines from the markup.
      $display_value = trim(str_replace(["\n", "\r"], ' ', strip_tags($result->getDisplayValue())));
      // Remove extra spaces from text.
      $display_value = preg_replace('/\s\s+/', ' ', $display_value);
      $option_data[] = [
        'key' => $result->getRawValue(),
        'label' => $display_value,
        'count' => $result->getCount(),
      ];
    }

    $price_facet_result = [
      'key' => $price_facet_build->id(),
      'label' => $this->getFacetBlockTitle($price_facet_build->id()),
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
    foreach ($sort_config as $key => $sort) {
      if (isset($sort_config_labels[$key . ' ASC'])) {
        $sort_data[] = [
          'key' => $key . ' ASC',
          'label' => $sort_config_labels[$key . ' ASC'],
        ];
      }
      if (isset($sort_config_labels[$key . ' DESC'])) {
        $sort_data[] = [
          'key' => $key . ' DESC',
          'label' => $sort_config_labels[$key . ' DESC'],
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
   * @return array
   *   Sort options for promo list page.
   */
  public function getPromoSortOptions() {
    $sort_data = [];
    // Get and set sort order from the views config.
    $views_storage = Views::getView($this->getViewsId())->storage;
    $views_sort = $views_storage->getDisplay('default')['display_options']['sorts'];
    // Get enabled sort options from config.
    $enabled_sorts = _alshaya_acm_product_position_get_config(TRUE);
    foreach ($views_sort as $sort) {
      if ($sort['exposed']) {
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
    $views_storage = Views::getView($this->getViewsId())->storage;
    $views_sort = $views_storage->getDisplay('default')['display_options']['sorts'];
    $default_sort = [];
    foreach ($views_sort as $sort) {
      if ($sort['exposed']) {
        $default_sort = [
          'key' => $sort['field'],
          'order' => $sort['order'],
        ];
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

}
