<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SearchPageProductListResource.
 *
 * @RestResource(
 *   id = "search_page_product_list",
 *   label = @Translation("Search Page Product List"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/search",
 *   }
 * )
 */
class SearchPageProductListResource extends ResourceBase {

  /**
   * Search API Index ID.
   */
  const SEARCH_API_INDEX_ID = 'acquia_search_index';

  /**
   * Query parse mode.
   */
  const PARSE_MODE = 'terms';

  /**
   * Parse mode conjunction.
   */
  const PARSE_MODE_CONJUNCTION = 'OR';

  /**
   * Query string key to use for the keyword.
   */
  const KEYWORD_KEY = 'q';

  /**
   * Facet source ID.
   */
  const FACET_SOURCE_ID = 'search_api:views_page__search__page';

  /**
   * Views machine name.
   */
  const VIEWS_ID = 'search';

  /**
   * Views display id.
   */
  const VIEWS_DISPLAY_ID = 'page';

  /**
   * Price facet machine id.
   */
  const PRICE_FACET_KEY = 'final_price';

  /**
   * Selling Price facet machine id.
   */
  const SELLING_PRICE_FACET_KEY = 'selling_price';

  /**
   * Parse mode plugin manager.
   *
   * @var \Drupal\search_api\ParseMode\ParseModePluginManager
   */
  protected $parseModeManager;

  /**
   * Alshaya search api query execute.
   *
   * @var \Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute
   */
  protected $alshayaSearchApiQueryExecute;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * CategoryProductListResource constructor.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param array $serializer_formats
   *   Serializer formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger channel.
   * @param \Drupal\search_api\ParseMode\ParseModePluginManager $parse_mode_manager
   *   Parse mode plugin manager.
   * @param \Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute $alshaya_search_api_query_execute
   *   Alshaya search api query execute.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              ParseModePluginManager $parse_mode_manager,
                              AlshayaSearchApiQueryExecute $alshaya_search_api_query_execute,
                              RequestStack $requestStack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->parseModeManager = $parse_mode_manager;
    $this->alshayaSearchApiQueryExecute = $alshaya_search_api_query_execute;
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('alshaya_mobile_app'),
      $container->get('plugin.manager.search_api.parse_mode'),
      $container->get('alshaya_mobile_app.alshaya_search_api_query_execute'),
      $container->get('request_stack')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns products data for the search page.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response products data.
   */
  public function get() {
    // Get search keyword.
    $search_keyword = $this->currentRequest->query->get(self::KEYWORD_KEY, '');
    // Get result set.
    $result_set = $this->prepareAndExecuteQuery($search_keyword);
    $response_data = $this->alshayaSearchApiQueryExecute->prepareResponseFromResult($result_set);

    // Get spell check results, if avaialable.
    $message['message']['spellcheck'] = $this->prepareSpellCheckResults($result_set, $search_keyword) ?? NULL;
    $response_data = array_merge($response_data, $message);

    return (new ModifiedResourceResponse($response_data));
  }

  /**
   * Preparing and executing the search api query.
   *
   * @param string $keyword
   *   Search keyword.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   Result set after query execution.
   */
  public function prepareAndExecuteQuery(string $keyword) {
    $index = Index::load(self::SEARCH_API_INDEX_ID);
    /* @var \Drupal\search_api\Query\QueryInterface $query */
    $query = $index->query();

    // Set the search api server.
    $this->alshayaSearchApiQueryExecute->setServerIndex(self::SEARCH_API_INDEX_ID);
    // Set facet source.
    $this->alshayaSearchApiQueryExecute->setFacetSourceId(self::FACET_SOURCE_ID);
    // Set the views id.
    $this->alshayaSearchApiQueryExecute->setViewsId(self::VIEWS_ID);
    // Set the views display id.
    $this->alshayaSearchApiQueryExecute->setViewsDisplayId(self::VIEWS_DISPLAY_ID);
    // Set the price facet key.
    $this->alshayaSearchApiQueryExecute->setPriceFacetKey(self::PRICE_FACET_KEY);
    // Set selling price facet key.
    $this->alshayaSearchApiQueryExecute->setSellingPriceFacetKey(self::SELLING_PRICE_FACET_KEY);

    // Change the parse mode for the search.
    $parse_mode = $this->parseModeManager->createInstance(self::PARSE_MODE);
    $parse_mode->setConjunction(self::PARSE_MODE_CONJUNCTION);
    $query->setParseMode($parse_mode);

    // Adding search keyword to the query if available.
    if (!empty($keyword)) {
      $query->keys($keyword);
      $query->setFulltextFields($index->getFulltextFields());
    }

    // Enable solr spellcheck.
    $query->setOption('search_api_spellcheck', TRUE);

    // Prepare and execute query and pass result set.
    return $this->alshayaSearchApiQueryExecute->prepareExecuteQuery($query);
  }

  /**
   * Prepare message array for spellcheck.
   *
   * @param array $result_set
   *   Result set data.
   * @param string $search_keyword
   *   Search key word.
   *
   * @return array
   *   Spell check results.
   */
  public function prepareSpellCheckResults(array $result_set, $search_keyword) {
    // Prepare spellcheck data, if available and if there are no search results.
    if (($this->alshayaSearchApiQueryExecute->getResultTotalCount() === 0)
      && isset($result_set['search_api_results']->getExtraData('search_api_solr_response')['spellcheck'])
      && !empty($suggestions = $result_set['search_api_results']->getExtraData('search_api_solr_response')['spellcheck'])
    ) {
      $spellcheck_results = [];
      foreach ($suggestions as $suggestion) {
        if ($search_keyword == $suggestion[0]) {
          if (!empty($suggestion[1]['suggestion'][0])) {
            $spellcheck_results[$search_keyword] = $suggestion[1]['suggestion'][0];
          }
        }
      }
      return $spellcheck_results;
    }
  }

}
