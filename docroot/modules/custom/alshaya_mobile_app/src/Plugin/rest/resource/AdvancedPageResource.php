<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a resource to node data of advanced page.
 *
 * @RestResource(
 *   id = "advanced_page",
 *   label = @Translation("advanced page"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/page/advanced"
 *   }
 * )
 */
class AdvancedPageResource extends ResourceBase {

  /**
   * Node bundle machine name.
   */
  const NODE_TYPE = 'advanced_page';

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * AdvancedPageResource constructor.
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
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility,
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $page = $this->requestStack->query->get('page');
    // Path alias of advanced page.
    $alias = $this->configFactory->get('alshaya_mobile_app.settings')->get('static_page_mappings.' . $page);
    $node = $this->mobileAppUtility->getNodeFromAlias($alias, self::NODE_TYPE);

    if (!$node instanceof NodeInterface) {
      $this->mobileAppUtility->throwException();
    }

    if (!$node->isPublished()) {
      $this->mobileAppUtility->throwException();
    }

    // Get bubbleable metadata for CacheableDependency to avoid fatal error.
    $node_url_obj = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
    $node_url = $node_url_obj->toString(TRUE);

    $response_data = [
      'id' => (int) $node->id(),
      'name' => $node->label(),
      'path' => $node_url->getGeneratedUrl(),
      'deeplink' => $this->mobileAppUtility->getDeepLink($node),
    ];

    $blocks = [];

    if ($node->get('field_use_as_department_page')->value == 1) {
      $term = $node->get('field_product_category')->referencedEntities()[0];
      if ($term instanceof TermInterface && !empty($term->getDescription())) {
        $blocks[] = [
          'type' => 'block',
          'body' => $term->getDescription(),
        ];
      }

      // Set the advanced page node so that it can be used later.
      $this->mobileAppUtility->setAdvancedPageNode($node);
    }

    // Change the position of the "delivery_banner" on the frontpage.
    $advanced_page_fields = $this->mobileAppUtility->getEntityBundleInfo($node->getEntityTypeId(), $node->bundle())['fields'];
    $frontPage = $this->configFactory->get('system.site')->get('page.front');
    if ($node_url_obj->getRouteName() && '/' . $node_url_obj->getInternalPath() === $frontPage) {
      $elem = ['body' => $advanced_page_fields['body']];
      $start = array_splice($advanced_page_fields, 0, array_search('field_delivery_banner', array_keys($advanced_page_fields)));
      $advanced_page_fields = $start + $elem + $advanced_page_fields;
    }

    foreach ($advanced_page_fields as $field => $field_info) {
      $current_blocks = $this->mobileAppUtility->getFieldData(
        $node,
        $field,
        $field_info['callback'],
        $field_info['label'],
        $field_info['type']
      );

      $current_blocks = array_filter($current_blocks);
      if (!empty($current_blocks['type'])) {
        $blocks[] = $current_blocks;
      }
      else {
        $blocks = array_merge($blocks, $current_blocks);
      }
    }

    $response_data['blocks'] = $blocks;

    $response = new ResourceResponse($response_data);
    $response->addCacheableDependency($node);
    foreach ($this->mobileAppUtility->getCacheableEntities() as $cacheable_entity) {
      $response->addCacheableDependency($cacheable_entity);
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'contexts' => [
          'url.query_args:page',
        ],
        'tags' => array_merge([
          ProductCategoryTree::CACHE_TAG,
          'node_view',
          'paragraph_view',
        ], $this->mobileAppUtility->getBlockCacheTags()),
      ],
    ]));

    return $response;
  }

}
