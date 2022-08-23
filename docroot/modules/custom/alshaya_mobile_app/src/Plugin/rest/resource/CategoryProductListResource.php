<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_product_category\Service\ProductCategoryPage;
use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\search_api\Query\ConditionGroup;
use Drupal\taxonomy\TermInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\Views;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\Core\Site\Settings;

/**
 * Class Category Product List Resource.
 *
 * @RestResource(
 *   id = "category_product_list",
 *   label = @Translation("Category Product List"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/category/{id}/product-list",
 *   }
 * )
 */
class CategoryProductListResource extends ResourceBase {

  /**
   * Query parse mode.
   */
  public const PARSE_MODE = 'direct';

  /**
   * Parse mode conjunction.
   */
  public const PARSE_MODE_CONJUNCTION = 'OR';

  /**
   * Page Type.
   */
  public const PAGE_TYPE = 'listing';

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Product category tree.
   *
   * @var \Drupal\alshaya_acm_product_category\ProductCategoryTree
   */
  protected $productCategoryTree;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Product Category Page service.
   *
   * @var \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage
   */
  protected $productCategoryPage;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\search_api\ParseMode\ParseModePluginManager $parse_mode_manager
   *   Parse mode plugin manager.
   * @param \Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute $alshaya_search_api_query_execute
   *   Alshaya search api query execute.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\alshaya_acm_product_category\ProductCategoryTree $product_category_tree
   *   Product category tree.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory object.
   * @param \Drupal\alshaya_acm_product_category\Service\ProductCategoryPage $product_category_page
   *   Product Category Page service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    EntityTypeManagerInterface $entity_type_manager,
    ParseModePluginManager $parse_mode_manager,
    AlshayaSearchApiQueryExecute $alshaya_search_api_query_execute,
    MobileAppUtility $mobile_app_utility,
    LanguageManagerInterface $language_manager,
    EntityRepositoryInterface $entity_repository,
    ProductCategoryTree $product_category_tree,
    ConfigFactoryInterface $config_factory,
    ProductCategoryPage $product_category_page
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->parseModeManager = $parse_mode_manager;
    $this->alshayaSearchApiQueryExecute = $alshaya_search_api_query_execute;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;
    $this->productCategoryTree = $product_category_tree;
    $this->configFactory = $config_factory;
    $this->productCategoryPage = $product_category_page;
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
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.search_api.parse_mode'),
      $container->get('alshaya_mobile_app.alshaya_search_api_query_execute'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('language_manager'),
      $container->get('entity.repository'),
      $container->get('alshaya_acm_product_category.product_category_tree'),
      $container->get('config.factory'),
      $container->get('alshaya_acm_product_category.page')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns products data for the given category id.
   *
   * @param int $id
   *   The category id.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response products data for given category.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the term not provided or department page.
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws when term provided not exists.
   */
  public function get($id = NULL) {
    if (!is_numeric($id) || empty($id)) {
      $this->mobileAppUtility->throwException();
    }

    // Load the term object.
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($id);
    // If given term exists in system.
    if (!$term instanceof TermInterface || $term->bundle() != 'acq_product_category') {
      $this->mobileAppUtility->throwException();
    }

    $term = $this->entityRepository->getTranslationFromContext($term, $this->languageManager->getCurrentLanguage()->getId());
    // If term is department page.
    if (alshaya_advanced_page_is_department_page($term->id())) {
      $this->mobileAppUtility->throwException();
    }

    // Get result set.
    $result_set = $this->prepareAndExecuteQuery($id);

    // Prepare response from result set.
    $response_data = $this->addExtraTermData($term);

    if (isset($result_set['department_name'])) {
      $response_data['department_name'] = $result_set['department_name'];
    }
    if (isset($result_set['algolia_data'])) {
      $response_data['algolia_data'] = $result_set['algolia_data'];
    }
    if (isset($result_set['plp_data'])) {
      $result_set = $result_set['plp_data'];
    }

    AlshayaRequestContextManager::updateDefaultContext('app');

    $response_data += $this->alshayaSearchApiQueryExecute->prepareResponseFromResult($result_set);
    $response_data['sort'] = $this->alshayaSearchApiQueryExecute->prepareSortData('alshaya_product_list', 'block_1', self::PAGE_TYPE);

    // Filter the empty products.
    // Array values being used to re-set the array index
    // if there any empty item in b/w.
    $response_data['products'] = array_values(array_filter($response_data['products']));

    // Get sub categories for the current term.
    $response_data['sub_categories'] = $this->getSubCategoryData($id);

    $response_data['total'] = $this->alshayaSearchApiQueryExecute->getResultTotalCount();

    return (new ModifiedResourceResponse($response_data));
  }

  /**
   * Get all child terms of a given parent term if plp Mobile Value is checked.
   *
   * @param int $parent_tid
   *   Parent term id.
   *
   * @return array
   *   Data array.
   */
  protected function getSubCategoryData(int $parent_tid) {
    // Calling view to get the sub category list.
    $subcategory_list_view = Views::getView('product_category_level_3');
    $subcategory_list_view->setDisplay('block_2');
    $subcategory_list_view->setArguments([$parent_tid]);
    $subcategory_list_view->execute();
    $data = [];
    foreach ($subcategory_list_view->result as $subcategory_list_view_value) {
      $sub_category_entity_list = $subcategory_list_view_value->_entity;
      $sub_category_entity = $this->entityRepository->getTranslationFromContext($sub_category_entity_list);
      $deeplink = $this->mobileAppUtility->getDeepLink($sub_category_entity);
      $data[] = [
        'id' => $sub_category_entity->get('tid')->getValue()[0]['value'],
        'label' => $sub_category_entity->get('name')->getValue()[0]['value'],
        'deeplink' => $deeplink,
      ];
    }
    return $data;
  }

  /**
   * Get term data for response.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term object.
   *
   * @return array
   *   Data array.
   */
  protected function addExtraTermData(TermInterface $term) {
    $term_url = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()])->toString(TRUE);
    $dy_banner = $term->get('field_dy_banner')->getString();
    $default_dy_banner = $this->configFactory->get('alshaya_mobile_app.settings')->get('dy_plp_banner_name');
    return [
      'id' => (int) $term->id(),
      'label' => $term->label(),
      'path' => $term_url->getGeneratedUrl(),
      'deeplink' => $this->mobileAppUtility->getDeepLink($term),
      'banner' => $term->get('field_promo_banner_for_mobile')->value ? [] : $this->mobileAppUtility->getImages($term, 'field_promotion_banner_mobile'),
      'description' => ($desc = $term->get('description')->getValue()) && !empty($desc[0]['value'])
      ? $desc[0]['value']
      : '',
      'total' => 0,
      'dy_banner' => [
        'plp_banner_name' => $dy_banner ?: $default_dy_banner,
      ],
    ];
  }

  /**
   * Preparing and executing the search api query.
   *
   * @param int $tid
   *   Term id.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   Result set after query execution.
   */
  public function prepareAndExecuteQuery(int $tid) {
    $storage = $this->entityTypeManager->getStorage('search_api_index');
    $response = [];

    // Get term details in current language for meta info (department name).
    $term_details = $this->productCategoryPage->getCurrentSelectedCategory('en', $tid);
    if (isset($term_details['hierarchy'])) {
      $response['department_name'] = str_replace('>', '|', $term_details['hierarchy']);
    }

    if (AlshayaSearchApiHelper::isIndexEnabled('product')) {
      $index = $storage->load('product');

      /** @var \Drupal\search_api\Query\QueryInterface $query */
      $query = $index->query();

      // Change the parse mode for the search.
      $parse_mode = $this->parseModeManager->createInstance(self::PARSE_MODE);
      $parse_mode->setConjunction(self::PARSE_MODE_CONJUNCTION);
      $query->setParseMode($parse_mode);

      // Child terms of given term.
      $terms = _alshaya_master_get_recursive_child_terms($tid);
      // Add condition for all child terms.
      $query->addCondition('tid', $terms, 'IN');

      // Adding tag to query.
      $query->addTag('category_product_list');

      // Prepare and execute query and pass result set.
      $response['plp_data'] = $this->alshayaSearchApiQueryExecute->prepareExecuteQuery($query, 'plp');

      return $response;
    }

    if (AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_index')) {
      // Get the config value for not executing search query.
      $respond_ignore_algolia_data = $this->configFactory->get('alshaya_mobile_app.settings')->get('listing_ignore_algolia_data');
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      if ((AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_product_list_index')) && (Settings::get('mobile_app_plp_index_new', FALSE))) {
        $langcode = 'en';
      }

      $response['algolia_data'] = $this->mobileAppUtility->getAlgoliaData($tid, $langcode);
      // Get term details in current language for filters.
      $term_details = $this->productCategoryPage->getCurrentSelectedCategory(
        $langcode,
        $tid
      );
      // Return only algolia data if the config value is set to false.
      if ($respond_ignore_algolia_data) {
        return $response;
      }

      $index = $storage->load('alshaya_algolia_index');

      /** @var \Drupal\search_api\Query\QueryInterface $query */
      $query = $index->query();

      // Set the search api server.
      $this->alshayaSearchApiQueryExecute->setServerIndex('alshaya_algolia_index');
      // Set facet source.
      $this->alshayaSearchApiQueryExecute->setFacetSourceId(SearchPageProductListResource::FACET_SOURCE_ID);
      // Set the views id.
      $this->alshayaSearchApiQueryExecute->setViewsId(SearchPageProductListResource::VIEWS_ID);
      // Set the views display id.
      $this->alshayaSearchApiQueryExecute->setViewsDisplayId(SearchPageProductListResource::VIEWS_DISPLAY_ID);
      // Set the price facet key.
      $this->alshayaSearchApiQueryExecute->setPriceFacetKey(SearchPageProductListResource::PRICE_FACET_KEY);
      // Set selling price facet key.
      $this->alshayaSearchApiQueryExecute->setSellingPriceFacetKey(SearchPageProductListResource::SELLING_PRICE_FACET_KEY);

      // Change the parse mode for the search.
      $parse_mode = $this->parseModeManager->createInstance(self::PARSE_MODE);
      $parse_mode->setConjunction(self::PARSE_MODE_CONJUNCTION);
      $query->setParseMode($parse_mode);

      $conditionGroup = new ConditionGroup();
      if ($this->configFactory->get('alshaya_search_api.listing_settings')->get('filter_oos_product')) {
        $conditionGroup->addCondition('stock', 0, '>');
      }
      $conditionGroup->addCondition($term_details['category_field'], '"' . $term_details['hierarchy'] . '"');
      $query->addConditionGroup($conditionGroup);
      $query->setOption('algolia_options', ['ruleContexts' => $term_details['ruleContext']]);

      // Prepare and execute query and pass result set.
      $response['plp_data'] = $this->alshayaSearchApiQueryExecute->prepareExecuteQuery($query, 'plp');

      return $response;
    }

    throw new \Exception('No backend available to process this request.');
  }

}
