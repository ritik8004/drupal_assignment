<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\NodeInterface;

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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Term storage object.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * File storage object.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\alshaya_mobile_app\Service\MobileAppUtility $mobile_app_utility
   *   The mobile app utility service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              Connection $connection,
                              MobileAppUtility $mobile_app_utility,
                              RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->connection = $connection;
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
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
      $container->get('language_manager'),
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('alshaya_mobile_app.utility'),
      $container->get('request_stack')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    // Path alias of advanced page.
    $alias = $this->requestStack->query->get('url');
    $node = $this->mobileAppUtility->getNodeFromAlias($alias, self::NODE_TYPE);

    if (!$node instanceof NodeInterface) {
      $this->mobileAppUtility->throwException();
    }

    // Get bubbleable metadata for CacheableDependency to avoid fatal error.
    $node_url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString(TRUE);

    $response_data = [
      'id' => $node->id(),
      'name' => $node->label(),
      'path' => $node_url->getGeneratedUrl(),
      'deeplink' => $this->mobileAppUtility->getDeepLink($node),
      'body' => $node->get('body')->getString(),
    ];

    $fields = [
      'field_promo_blocks',
      'field_delivery_banner',
      'field_promo_banner_full_width',
      'field_related_info',
      'field_slider',
    ];

    $cache_objects = [];
    foreach ($fields as $field) {
      $field_data = $this->mobileAppUtility->getFieldData($node, $field);
      $response_data[$field] = $field_data['blocks'];
      $cache_objects = array_merge($field_data['cache'], $cache_objects);
    }

    $response = new ResourceResponse($response_data);
    $response->addCacheableDependency($node);
    $response->addCacheableDependency($node_url);
    foreach ($cache_objects as $cache) {
      $response->addCacheableDependency($cache);
    }
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'contexts' => [
          'url.query_args:url',
        ],
      ],
    ]));

    return $response;
  }

}
