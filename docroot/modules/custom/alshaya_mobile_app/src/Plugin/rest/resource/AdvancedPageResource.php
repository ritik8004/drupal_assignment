<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              array $serializer_formats,
                              LoggerInterface $logger,
                              MobileAppUtility $mobile_app_utility,
                              RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
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
    ];

    $blocks = [];
    $term_id = alshaya_advanced_page_get_department_category($node->id());
    if ($term_id) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);
      $blocks[] = [
        'type' => 'block',
        'body' => $term->getDescription(),
      ];
    }

    foreach ($this->mobileAppUtility->getEntityBundleInfo($node->getEntityTypeId(), $node->bundle())['fields'] as $field => $field_info) {
      $current_blocks = $this->mobileAppUtility->getFieldData($node, $field, $field_info['callback'], $field_info['label'], $field_info['type']);
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
