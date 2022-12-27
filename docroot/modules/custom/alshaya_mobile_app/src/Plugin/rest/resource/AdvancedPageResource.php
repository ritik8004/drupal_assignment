<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\alshaya_acm_product_category\ProductCategoryTree;
use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\views\Views;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

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

  use LoggerChannelTrait;

  /**
   * Node bundle machine name.
   */
  public const NODE_TYPE = 'advanced_page';

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
   * Entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The render context.
   *
   * @var \Drupal\Core\Render\RenderContext
   */
  protected $renderContext;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   Entity repository.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module services.
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
    ConfigFactoryInterface $config_factory,
    EntityRepositoryInterface $entityRepository,
    RendererInterface $renderer,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->entityRepository = $entityRepository;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
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
      $container->get('config.factory'),
      $container->get('entity.repository'),
      $container->get('renderer'),
      $container->get('module_handler')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing list of categories.
   */
  public function get() {
    $term = NULL;
    // Path alias of advanced page.
    $alias = $this->requestStack->query->get('url');
    $page = $this->requestStack->query->get('page');
    if (!$alias && $page !== 'front') {
      $alias = $this->configFactory->get('alshaya_mobile_app.settings')->get('static_page_mappings.' . $page);
    }

    try {
      $node = ($page === 'front')
        ? $this->entityTypeManager->getStorage('node')->load($this->configFactory->get('alshaya_master.home')->get('entity')['id'])
        : $this->mobileAppUtility->getNodeFromAlias($alias, self::NODE_TYPE);
    }
    catch (\Exception $e) {
      // Redirect to 404.
      // Adding log entry for exceptions.
      $message = 'Invalid path: @alias, redirecting to 404';

      $this->getLogger('AdvancedPageResourcePathCheck')->warning($message, [
        '@code' => $e->getCode(),
        '@message' => $e->getMessage(),
        '@alias' => $alias,
      ]);
      throw new NotFoundHttpException();
    }

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

    // Change the position of the "delivery_banner" on the frontpage.
    $advanced_page_fields = $this->mobileAppUtility->getEntityBundleInfo($node->getEntityTypeId(), $node->bundle())['fields'];
    $frontPage = $this->configFactory->get('system.site')->get('page.front');
    if ($node_url_obj->getRouteName() && '/' . $node_url_obj->getInternalPath() === $frontPage) {
      $elem = ['body' => $advanced_page_fields['body']];
      $start = array_splice($advanced_page_fields, 0, array_search('field_delivery_banner', array_keys($advanced_page_fields)));
      $advanced_page_fields = $start + $elem + $advanced_page_fields;
    }

    // LHN Data for Departmental page for mobile app.
    if ($node->get('field_use_as_department_page')->getValue()[0]['value'] == 1 && $node->get('field_show_left_menu')->getValue()[0]['value'] == 1) {
      if ($term instanceof TermInterface) {
        $response_data['lhn_tree'] = $this->getCategoryData($term->id());
      }
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
      if (isset($current_blocks['type']) && !empty($current_blocks['type'])) {
        $blocks[] = $current_blocks;
      }
      else {
        $blocks = array_merge($blocks, $current_blocks);
      }
    }

    if ($node->get('field_use_as_department_page')->getString()) {
      $term = $node->get('field_product_category')->referencedEntities()[0] ?? NULL;
      if ($term instanceof TermInterface) {
        $current_language = $this->mobileAppUtility->currentLanguage();
        $term = $term->hasTranslation($current_language) ? $term->getTranslation($current_language) : $term;
        if (!empty($term->getDescription())) {
          $blocks[] = [
            'type' => 'block',
            'body' => $term->getDescription(),
          ];
        }
      }

      // Set the advanced page node so that it can be used later.
      $this->mobileAppUtility->setAdvancedPageNode($node);
    }

    $response_data['blocks'] = $blocks;
    // Allow other modules to alter response data.
    $this->moduleHandler->alter('advanced_page_resource_response', $response_data);

    $response = new ResourceResponse($response_data);
    $response->addCacheableDependency($node);
    foreach ($this->mobileAppUtility->getCacheableEntities() as $cacheable_entity) {
      $response->addCacheableDependency($cacheable_entity);
    }

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'contexts' => [
          'url.query_args:page',
          'url.query_args:url',
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

  /**
   * Get all terms of a given parent term.
   *
   * @param int $parent_tid
   *   Parent term id.
   *
   * @return array
   *   Data array.
   */
  private function getCategoryData(int $parent_tid) {
    $subcategory_list_view = $this->handleViewBubbleMetadata('product_category_level_2_3', 'block_2', $parent_tid);
    $data = [];
    foreach ($subcategory_list_view->result as $subcategory_list_view_value) {
      $sub_category_entity_list = $subcategory_list_view_value->_entity;
      $sub_category_entity = $this->entityRepository->getTranslationFromContext($sub_category_entity_list);
      $sub_category_entity_url_obj = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $sub_category_entity->id()]);
      $sub_category_entity_url = $sub_category_entity_url_obj->toString(TRUE)->getGeneratedUrl();
      $data[] = [
        'id' => $sub_category_entity->get('tid')->getValue()[0]['value'],
        'label' => $sub_category_entity->get('name')->getValue()[0]['value'],
        'deeplink' => $this->mobileAppUtility->getDeepLink($sub_category_entity),
        'path' => $sub_category_entity_url,
        'child' => $this->getChildCategoryData($sub_category_entity->get('tid')->getValue()[0]['value']),
      ];
    }
    return $data;
  }

  /**
   * Get all child terms of a given parent term.
   *
   * @param int $child_tid
   *   Child term id.
   *
   * @return array
   *   Data array.
   */
  private function getChildCategoryData(int $child_tid) {
    $childCategory_list_view = $this->handleViewBubbleMetadata('product_category_level_3', 'block_1', $child_tid);
    $data = [];
    foreach ($childCategory_list_view->result as $childCategory_list_view_value) {
      $child_category_entity_list = $childCategory_list_view_value->_entity;
      $child_category_entity = $this->entityRepository->getTranslationFromContext($child_category_entity_list);
      $child_category_entity_url_obj = Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $child_category_entity->id()]);
      $child_category_entity_url = $child_category_entity_url_obj->toString(TRUE)->getGeneratedUrl();
      $data[] = [
        'id' => $child_category_entity->get('tid')->getValue()[0]['value'],
        'label' => $child_category_entity->get('name')->getValue()[0]['value'],
        'deeplink' => $this->mobileAppUtility->getDeepLink($child_category_entity),
        'path' => $child_category_entity_url,
      ];
    }
    return $data;
  }

  /**
   * Fetches view result with handling its cache metadata.
   *
   * @param string $view_id
   *   View id.
   * @param string $display_id
   *   View display id.
   * @param string $arguments
   *   View arguments.
   *
   * @return array
   *   Data array.
   */
  private function handleViewBubbleMetadata($view_id, $display_id, $arguments) {
    $this->renderContext ??= new RenderContext();
    $result = $this->renderer->executeInRenderContext($this->renderContext, function () use ($view_id, $display_id, $arguments) {
      $view = Views::getView($view_id);
      $view->setDisplay($display_id);
      $view->setArguments([$arguments]);
      $view->execute();

      return $view;
    });
    if (!$this->renderContext->isEmpty()) {
      $bubbleable_metadata = $this->renderContext->pop();
      BubbleableMetadata::createFromObject($result)
        ->merge($bubbleable_metadata);
    }

    return $result;
  }

}
