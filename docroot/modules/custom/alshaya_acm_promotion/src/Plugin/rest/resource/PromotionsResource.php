<?php

namespace Drupal\alshaya_acm_promotion\Plugin\rest\resource;

use Drupal\alshaya_acm_product\AlshayaPromoContextManager;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\NodeInterface;
use Drupal\rest\ModifiedResourceResponse;

/**
 * Provides a resource to get list of all promotions.
 *
 * @RestResource(
 *   id = "promotions",
 *   label = @Translation("List all promotions"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/promotion/all"
 *   }
 * )
 */
class PromotionsResource extends ResourceBase {

  /**
   * The content to be cached.
   *
   * @var array
   */
  protected $content = [];

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Alshaya Promotions Context Manager.
   *
   * @var \Drupal\alshaya_acm_product\AlshayaPromoContextManager
   */
  protected $promoContextManager;

  /**
   * PromotionsResource constructor.
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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param \Drupal\alshaya_acm_product\AlshayaPromoContextManager $alshayaPromoContextManager
   *   Alshaya Promo Context Manager.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              Connection $connection,
                              ModuleHandlerInterface $module_handler,
                              AlshayaPromoContextManager $alshayaPromoContextManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->connection = $connection;
    $this->moduleHandler = $module_handler;
    $this->promoContextManager = $alshayaPromoContextManager;
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
      $container->get('logger.factory')->get('alshaya_acm_promotion'),
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('database'),
      $container->get('module_handler'),
      $container->get('alshaya_acm_product.context_manager')
    );
  }

  /**
   * Get the list of node ids of promotion.
   *
   * @param string $langcode
   *   The language code.
   * @param string $context
   *   The context of the promotion, either web or app.
   *
   * @return array
   *   List of nids.
   */
  private function getAllPromotions($langcode, $context = '') {
    $query = $this->connection->select('node', 'n');
    $query->fields('nd', ['nid']);
    $query->leftJoin('node_field_data', 'nd', 'nd.nid = n.nid');
    $query->condition('nd.langcode', $langcode);
    $query->condition('nd.type', 'acq_promotion');
    $query->condition('nd.status', NodeInterface::PUBLISHED);
    if (!empty($context)) {
      $query->leftJoin('node__field_acq_promotion_context', 'npc', 'npc.entity_id = n.nid');
      $query->condition('npc.field_acq_promotion_context_value', $context);
    }
    return $query->execute()->fetchCol();
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $context = $this->promoContextManager->getPromotionContext();
    $nids = $this->getAllPromotions($langcode, $context);
    $response_data = [];

    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['url.query_args:context']);
    $cacheability->addCacheTags(['node_type:acq_promotion']);

    if (!empty($nids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      foreach ($nodes as $node) {
        $node = $this->entityRepository->getTranslationFromContext($node);

        // Get bubbleable metadata for CacheableDependency to avoid fatal error.
        $node_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(TRUE);

        $data = [
          'id' => (int) $node->id(),
          'name' => $node->label(),
          'path' => $node_url->getGeneratedUrl(),
          'commerce_id' => (int) $node->get('field_acq_promotion_rule_id')->first()->getString(),
          'promo_sub_tpe' => $node->get('field_alshaya_promotion_subtype')->first()->getString(),
          'promo_desc' => $node->get('field_acq_promotion_description')->first() ? $node->get('field_acq_promotion_description')->first()->getValue()['value'] : '',
          'promo_label' => $node->get('field_acq_promotion_label')->getString(),
        ];

        $this->moduleHandler->alter('alshaya_acm_promo_resource', $data, $node);

        $response_data[] = $data;
        $this->content[] = $node;
      }

      $response = new ResourceResponse($response_data);
      $response->addCacheableDependency($cacheability);
      return $response;
    }

    // Sending modified response so response is not cached when promotions
    // not available.
    return (new ModifiedResourceResponse($response_data));
  }

  /**
   * Adding nodes dependency to response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   * @param string $tag
   *   The tag string.
   */
  protected function addCacheableDependency(ResourceResponse $response, $tag) {
    if (!empty($this->content)) {
      foreach ($this->content as $node) {
        $response->addCacheableDependency($node);
      }
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => [$tag, 'node_list'],
      ],
    ]));
  }

}
