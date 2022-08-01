<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_search_api\AlshayaSearchApiHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Url;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute;
use Drupal\search_api\Query\ConditionGroup;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;

/**
 * Class Promotion Product List Resource.
 *
 * @RestResource(
 *   id = "promotion_product_list",
 *   label = @Translation("Promotion Product List"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/promotion/{id}/product-list",
 *   }
 * )
 */
class PromotionProductListResource extends ResourceBase {

  /**
   * Query parse mode.
   */
  public const PARSE_MODE = 'direct';

  /**
   * Parse mode conjunction.
   */
  public const PARSE_MODE_CONJUNCTION = 'OR';

  /**
   * Promotion node bundle.
   */
  public const NODE_BUNDLE = 'acq_promotion';

  /**
   * Page Type.
   */
  public const PAGE_TYPE = 'listing';

  /**
   * Facet source ID.
   */
  public const FACET_SOURCE_ID = 'search_api:views_block__alshaya_product_list__block_2';

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
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory object.
   */
  public function __construct(array $configuration,
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
                              ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->parseModeManager = $parse_mode_manager;
    $this->alshayaSearchApiQueryExecute = $alshaya_search_api_query_execute;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;
    $this->configFactory = $config_factory;
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
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * Returns products data for the given promotion id.
   *
   * @param int $id
   *   The node id.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The response products data for given promotion.
   */
  public function get(int $id = NULL) {
    if ($id) {
      // Load the promotion node object.
      $node = $this->entityTypeManager->getStorage('node')->load($id);
      // If given node exists in system.
      if (!$node instanceof NodeInterface) {
        // Validate if it's the rule_id of a node.
        $query = $this->entityTypeManager->getStorage('node')->getQuery('AND');
        $query->condition('type', 'acq_promotion');
        $query->condition('field_acq_promotion_rule_id', $id);
        $id = $query->execute();
        if ($id) {
          $id = array_values($id)[0];
          // Load the promotion node object.
          $node = $this->entityTypeManager->getStorage('node')->load($id);
          if (!$node instanceof NodeInterface) {
            $this->mobileAppUtility->throwException();
          }
        }
        else {
          $this->mobileAppUtility->throwException();
        }
      }
      // If node is not of promotion type or active.
      if ($node->bundle() != self::NODE_BUNDLE || !$node->isPublished()) {
        $this->mobileAppUtility->throwException();
      }

      // Get language specific version of node.
      $node = $this->entityRepository->getTranslationFromContext($node, $this->languageManager->getCurrentLanguage()->getId());

      // Get result set.
      $rule_id = (int) $node->get('field_acq_promotion_rule_id')->getString();
      // Extract the rule id as we are now indexing based on rule id.
      $result_set = $this->prepareAndExecuteQuery($rule_id);
      // Add promo data in result set.
      $response_data = $this->addExtraPromoData($node);
      // Make response data similar to web site.
      $this->alshayaSearchApiQueryExecute->setFacetsToIgnore(['category_facet_promo']);

      AlshayaRequestContextManager::updateDefaultContext('app');

      // Prepare response from result set.
      $response_data += $this->alshayaSearchApiQueryExecute->prepareResponseFromResult($result_set);
      $response_data['sort'] = $this->alshayaSearchApiQueryExecute->prepareSortData('alshaya_product_list', 'block_2', self::PAGE_TYPE);

      // Filter the empty products.
      $response_data['products'] = array_filter($response_data['products']);

      return (new ModifiedResourceResponse($response_data));
    }

    $this->mobileAppUtility->throwException();

  }

  /**
   * Preparing and executing the search api query.
   *
   * @param int $rule_id
   *   Rule id.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   Result set after query execution.
   */
  public function prepareAndExecuteQuery(int $rule_id) {
    $response = [];
    $storage = $this->entityTypeManager->getStorage('search_api_index');

    if (AlshayaSearchApiHelper::isIndexEnabled('product')) {
      $index = $storage->load('product');

      /** @var \Drupal\search_api\Query\QueryInterface $query */
      $query = $index->query();

      // Change the parse mode for the search.
      $parse_mode = $this->parseModeManager->createInstance(self::PARSE_MODE);
      $parse_mode->setConjunction(self::PARSE_MODE_CONJUNCTION);
      $query->setParseMode($parse_mode);

      // Set facet source.
      $this->alshayaSearchApiQueryExecute->setFacetSourceId(self::FACET_SOURCE_ID);
      // Set views display id to use for views.
      $this->alshayaSearchApiQueryExecute->setViewsDisplayId('block_2');
      // Set price facet key.
      $this->alshayaSearchApiQueryExecute->setPriceFacetKey('promotion_price_facet');
      // Set selling price facet key.
      $this->alshayaSearchApiQueryExecute->setSellingPriceFacetKey('promo_selling_price');

      // Add condition for promotion node.
      $query->addCondition('promotion_nid', $rule_id);

      // Prepare and execute query and pass result set.
      return $this->alshayaSearchApiQueryExecute->prepareExecuteQuery($query, 'promo');
    }

    if (AlshayaSearchApiHelper::isIndexEnabled('alshaya_algolia_index')) {
      // Get the config value for not executing search query.
      $respond_ignore_algolia_data = $this->configFactory->get('alshaya_mobile_app.settings')->get('listing_ignore_algolia_data');

      $response['algolia_data'] = [
        'filter_field' => 'promotion_nid',
        'filter_value' => $rule_id,
        'rule_contexts' => '',
      ];

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
      $conditionGroup->addCondition('promotion_nid', $rule_id);
      $query->addConditionGroup($conditionGroup);

      // Prepare and execute query and pass result set.
      $response = $this->alshayaSearchApiQueryExecute->prepareExecuteQuery($query, 'promo');

      return $response;
    }

    throw new \Exception('No backend available to process this request.');
  }

  /**
   * Get promotion data for response.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node object.
   *
   * @return array
   *   Data array.
   */
  protected function addExtraPromoData(NodeInterface $node) {
    // Get node url.
    $node_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(TRUE);

    $banners = $this->mobileAppUtility->getImages($node, 'field_acq_promotion_banner');
    return [
      'id' => (int) $node->get('field_acq_promotion_rule_id')->getString(),
      'label' => $node->get('field_acq_promotion_label')->getString(),
      'path' => $node_url->getGeneratedUrl(),
      'deeplink' => $this->mobileAppUtility->getDeepLink($node),
      'banner' => !empty($banners) ? $banners[0] : '',
      'description' => ($desc = $node->get('field_acq_promotion_description')->getValue())
      ? $desc[0]['value']
      : '',
    ];
  }

}
