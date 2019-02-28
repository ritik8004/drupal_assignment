<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\Core\Url;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\search_api\Entity\Index;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\search_api\ParseMode\ParseModePluginManager;
use Drupal\alshaya_mobile_app\Service\AlshayaSearchApiQueryExecute;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CategoryProductListResource.
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
    if (!$term instanceof TermInterface) {
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
    $response_data += $this->alshayaSearchApiQueryExecute->prepareResponseFromResult($result_set);
    $response_data['total'] = $this->alshayaSearchApiQueryExecute->getResultTotalCount();
    return (new ModifiedResourceResponse($response_data));
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
    return [
      'id' => (int) $term->id(),
      'label' => $term->label(),
      'path' => $term_url->getGeneratedUrl(),
      'deeplink' => $this->mobileAppUtility->getDeepLink($term),
      'banner' => $this->mobileAppUtility->getImages($term, 'field_promotion_banner_mobile'),
      'description' => ($desc = $term->get('description')->getValue()) && !empty($desc[0]['value'])
      ? $desc[0]['value']
      : '',
      'total' => 0,
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
    $index = Index::load(self::SEARCH_API_INDEX_ID);
    /* @var \Drupal\search_api\Query\QueryInterface $query */
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
    return $this->alshayaSearchApiQueryExecute->prepareExecuteQuery($query);
  }

}
