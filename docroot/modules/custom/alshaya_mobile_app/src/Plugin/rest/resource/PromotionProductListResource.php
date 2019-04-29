<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\node\NodeInterface;
use Drupal\Core\Url;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\search_api\Entity\Index;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PromotionProductListResource.
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
   * Search API Index ID.
   */
  const SEARCH_API_INDEX_ID = 'product';

  /**
   * Query parse mode.
   */
  const PARSE_MODE = 'direct';

  /**
   * Parse mode conjunction.
   */
  const PARSE_MODE_CONJUNCTION = 'OR';

  /**
   * Promotion node bundle.
   */
  const NODE_BUNDLE = 'acq_promotion';

  /**
   * Facet source ID.
   */
  const FACET_SOURCE_ID = 'search_api:views_block__alshaya_product_list__block_2';

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager, ParseModePluginManager $parse_mode_manager, AlshayaSearchApiQueryExecute $alshaya_search_api_query_execute, MobileAppUtility $mobile_app_utility, LanguageManagerInterface $language_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->parseModeManager = $parse_mode_manager;
    $this->alshayaSearchApiQueryExecute = $alshaya_search_api_query_execute;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;
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
      $container->get('entity.repository')
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
      if ($node instanceof NodeInterface) {
        // If node is not of promotion type or active.
        if ($node->bundle() != self::NODE_BUNDLE || !$node->isPublished()) {
          $this->mobileAppUtility->throwException();
        }

        // Get language specific version of node.
        $node = $this->entityRepository->getTranslationFromContext($node, $this->languageManager->getCurrentLanguage()->getId());

        // Get result set.
        $result_set = $this->prepareAndExecuteQuery($id);
        // Add promo data in result set.
        $response_data = $this->addExtraPromoData($node);
        // Prepare response from result set.
        $response_data += $this->alshayaSearchApiQueryExecute->prepareResponseFromResult($result_set);
        return (new ModifiedResourceResponse($response_data));
      }

      $this->mobileAppUtility->throwException();

    }

    $this->mobileAppUtility->throwException();
  }

  /**
   * Preparing and executing the search api query.
   *
   * @param int $nid
   *   Node id.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   Result set after query execution.
   */
  public function prepareAndExecuteQuery(int $nid) {
    $index = Index::load(self::SEARCH_API_INDEX_ID);
    /* @var \Drupal\search_api\Query\QueryInterface $query */
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
    $this->alshayaSearchApiQueryExecute->setSellingPriceFacetKey('promotion_selling_price_facet');

    // Add condition for promotion node.
    $query->addCondition('promotion_nid', $nid);

    // Prepare and execute query and pass result set.
    return $this->alshayaSearchApiQueryExecute->prepareExecuteQuery($query);
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
      'id' => (int) $node->id(),
      'label' => $node->getTitle(),
      'path' => $node_url->getGeneratedUrl(),
      'deeplink' => $this->mobileAppUtility->getDeepLink($node),
      'banner' => !empty($banners) ? $banners[0] : '',
      'description' => ($desc = $node->get('field_acq_promotion_description')->getValue())
      ? $desc[0]['value']
      : '',
    ];
  }

}
