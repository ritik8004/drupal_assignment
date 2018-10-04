<?php

namespace Drupal\alshaya_mobile_app\Service;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Entity\Index;
use Drupal\facets\Result\Result;
use Drupal\facets\QueryType\QueryTypePluginManager;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
   */
  public function __construct(RequestStack $requestStack, DefaultFacetManager $facet_manager, LanguageManagerInterface $language_manager, QueryTypePluginManager $query_type_manager) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->facetManager = $facet_manager;
    $this->languageManager = $language_manager;
    $this->queryTypePluginManager = $query_type_manager;
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
    // Status of node.
    $query->addCondition('status', 1);

    // Get query string parameters..
    $query_string_parameters = $this->currentRequest->query->all();

    // Query pagination.
    $page_offset = 0;
    $page_limit = self::PAGER_DEFAULT_LIMIT;
    // If pager info is available in query string.
    if (!empty($query_string_parameters[self::PAGER_KEY])) {
      // Get pager offset and limit info.
      $pager = explode(',', $query_string_parameters[self::PAGER_KEY]);
      $page_offset = $pager[0];
      if (!empty($pager[1]) && is_int($pager[1])) {
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
          throw new BadRequestHttpException($this->t('Invalid sort order.'));
        }

        $query->sort($sort_option[0], $sort_option[1]);
      }
      else {
        // If either sort key or sort value not available.
        throw new BadRequestHttpException($this->t('Invalid sort data.'));
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
      // Execute facet build so that facet processor gets executed.
      $facet_build[$facet->id()] = $this->facetManager->build($facet);
    }

    return [
      'facet_build' => $facet_build,
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
      // @Todo: Make it dynamic.
      // If price facet.
      if ($facet['key'] == 'skus_sku_reference_final_price') {
        $facet = $this->processPriceFacet($facet);
      }
    }

    // Prepare sort data.
    $sort_data = $this->prepareSortData($this->viewsId);

    // Prepare final result.
    return [
      'filters' => $facet_result,
      'sort' => $sort_data,
      'products' => $product_data,
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
    $facets_data = $result_set['facet_build'];
    // Prepare facet data first.
    $facet_result = [];
    foreach ($facets_data as $key => $facet_options) {
      // If no option available for a facet, skip that.
      if (empty($facet_options[0]['#items'])) {
        continue;
      }

      $facet_option_data = [];
      foreach ($facet_options[0]['#items'] as $option) {
        $facet_option_data[] = [
          'key' => $option['#attributes']['data-drupal-facet-item-value'],
          'label' => $option['#title']['#value'],
          'count' => $option['#title']['#count'],
        ];
      }
      $facet_result[] = [
        'key' => $key,
        'label' => !empty($this->processedFacetsArray[$key]) ? $this->processedFacetsArray[$key]->getName() : '',
        'options' => $facet_option_data,
      ];
    }

    return $facet_result;
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
      $product_data[] = $item->getId();
    }

    return $product_data;
  }

  /**
   * Prepare response data array for sort.
   *
   * @param string $views_id
   *   Views machine id.
   *
   * @return array
   *   Sort array.
   */
  public function prepareSortData(string $views_id) {
    // If PLP views.
    if ($views_id == 'alshaya_product_list') {
      $sort_config = _alshaya_acm_product_position_get_config(TRUE);
      foreach ($sort_config as $key => $label) {
        if (empty($label)) {
          unset($sort_config[$key]);
        }
      }

      // Sorted sort data.
      return _alshaya_acm_product_position_sorted_options($sort_config);
    }
    else {
      $sort_config = _alshaya_search_get_config(TRUE);
      foreach ($sort_config as $key => $label) {
        if (empty($label)) {
          unset($sort_config[$key]);
        }
      }

      return $sort_config;
    }

    return [];
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
    ]);
    $price_facet_data = [];
    foreach ($price_facet_result['options'] as $price) {
      $range_value = $alshaya_search_granular->calculateResultFilter($price['key']);
      // Trim and remove html and newlines from the markup.
      $stripped_value = trim(str_replace(["\n", "\r"], ' ', strip_tags($range_value['display'])));
      $price_facet_data[$stripped_value][] = [
        'key' => $range_value['raw'],
        'label' => $stripped_value,
      ];
    }

    if (!empty($price_facet_data)) {
      // Sort the result.
      usort($price_facet_data, function ($a, $b) {
        return $a[0]['key'] < $b[0]['key'] ? -1 : 1;
      });
      $price_facet_result['options'] = [];
      foreach ($price_facet_data as $price_range) {
        $price_facet_result['options'][] = [
          'key' => $price_range[0]['key'],
          'label' => $price_range[0]['label'],
          'count' => count($price_range),
        ];
      }
    }

    return $price_facet_result;
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

}
