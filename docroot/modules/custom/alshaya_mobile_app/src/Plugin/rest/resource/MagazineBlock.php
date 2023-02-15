<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Drupal\rest\ResourceResponse;
use Drupal\views\Views;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;

/**
 * Provides a resource to get magazine block.
 *
 * @RestResource(
 *   id = "magazine_block",
 *   label = @Translation("Magazine Block"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/page/magazine-block"
 *   }
 * )
 */
class MagazineBlock extends ResourceBase {

  /**
   * Prefix used for the endpoint.
   */
  public const ENDPOINT_PREFIX = '/rest/v1/';

  /**
   * The mobile app utility service.
   *
   * @var \Drupal\alshaya_mobile_app\Service\MobileAppUtility
   */
  protected $mobileAppUtility;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility,
    EntityRepositoryInterface $entity_repository,
    ConfigFactoryInterface $config_factory,
    RendererInterface $renderer,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->entityRepository = $entity_repository;
    $this->configFactory = $config_factory;
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
      $container->get('entity.repository'),
      $container->get('config.factory'),
      $container->get('renderer'),
      $container->get('module_handler')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response returns the magazine block.
   */
  public function get() {
    $view_id = 'magazine_articles';
    $display_id = 'homepage_block';

    // Return if the module 'alshaya_magazine' isn't enabled.
    if (!$this->moduleHandler->moduleExists('alshaya_magazine')) {
      $this->mobileAppUtility->throwException();
    }

    $magazine_listing_page_url_obj = Url::fromRoute('view.' . $view_id . '.list')->toString(TRUE);
    $magazine_listing_page_url = $magazine_listing_page_url_obj->getGeneratedUrl();
    $magazine_array_render = [];
    $magazine_block = [];

    $context = new RenderContext();
    $result = $this->renderer->executeInRenderContext($context, function () use ($view_id, $display_id) {
      $magazine_view = Views::getView($view_id);
      $magazine_view->setDisplay($display_id);
      $magazine_view_title = $magazine_view->getTitle();
      $magazine_view->execute();

      return [
        'view_result' => $magazine_view->result,
        'view_title' => $magazine_view_title,
      ];
    });
    // Handle any bubbled cacheability metadata.
    if (!$context->isEmpty()) {
      $bubbleable_metadata = $context->pop();
      BubbleableMetadata::createFromObject($result)
        ->merge($bubbleable_metadata);
    }
    if (isset($result['view_result']) && (is_countable($result['view_result']) ? count($result['view_result']) : 0) < 1) {
      $this->mobileAppUtility->throwException();
    }

    $magazine_view_more_text = $this->configFactory->getEditable('views.view.' . $view_id)->get('display.' . $display_id . '.display_options.use_more_text');
    $magazi_view_offset = $this->configFactory->getEditable('views.view.' . $view_id)->get('display.default.display_options.pager.options.offset');
    $magazi_view_limit = $this->configFactory->getEditable('views.view.' . $view_id)->get('display.default.display_options.pager.options.items_per_page');
    $magazine_array_render = [
      'type' => 'magazine_block',
      'title' => $result['view_title'],
      'url' => $magazine_listing_page_url,
      'deeplink' => self::ENDPOINT_PREFIX . 'page/magazine-teasers?offset=' . $magazi_view_offset . '&limit=' . $magazi_view_limit,
    ];

    // phpcs:ignore
    $magazine_array_render['view_more_link_text'] = $this->t($magazine_view_more_text);
    foreach ($result['view_result'] as $magazine_result_value) {
      $magazine_entity = $this->entityRepository->getTranslationFromContext($magazine_result_value->_entity);
      $magazine_block['title'] = $magazine_entity->getTitle();
      $magazine_entity_url_obj = Url::fromRoute('entity.node.canonical', ['node' => $magazine_entity->id()]);
      $magazine_entity_url = $magazine_entity_url_obj->toString(TRUE);
      $magazine_block['url'] = $magazine_entity_url->getGeneratedUrl();
      $magazine_block['deeplink'] = $this->mobileAppUtility->getDeepLink($magazine_entity);
      if ($magazine_entity->get('field_magazine_homepage_image')->getValue()) {
        $magazine_block['image'] = $this->mobileAppUtility->getImages($magazine_entity, 'field_magazine_homepage_image', 'magazine_article_home');
      }
      if ($magazine_entity->hasField('field_magazine_category') && !empty($magazine_entity->field_magazine_category)) {
        $magazine_category_obj = $magazine_entity->get('field_magazine_category')->referencedEntities();
        $magazine_category_value = $this->getMagazineCategory($magazine_category_obj);
        if (!empty($magazine_category_value)) {
          $magazine_block['magazine_category'] = $magazine_category_value['magazine_category_terms'];
        }
      }
      if ($magazine_entity->get('field_magazine_slogan')->getValue()) {
        $magazine_block['slogan'] = $magazine_entity->get('field_magazine_slogan')->getValue()[0]['value'];
      }
      $magazine_block['node_more_link_text'] = $this->t('read the story');
      $magazine_array_render['items'][] = $magazine_block;
    }
    $response = new ResourceResponse($magazine_array_render);
    $response->addCacheableDependency($response);
    // Add cacheTags node_type as the view depend on node type magazine_article.
    $response->getCacheableMetadata()->addCacheTags(['node_type:magazine_article']);
    return $response;
  }

  /**
   * Get magazine category terms.
   */
  public function getMagazineCategory($object_array = NULL) {
    $magazine_category_terms = [];
    if (is_array($object_array) && !empty($object_array)) {
      foreach ($object_array as $magazine_category_val) {
        $magazine_category = $this->entityRepository->getTranslationFromContext($magazine_category_val);
        $magazine_category_data['id'] = (int) $magazine_category->id();
        $magazine_category_data['name'] = $magazine_category->getName();
        $magazine_category_terms['magazine_category_terms'] = $magazine_category_data;
      }
    }
    return $magazine_category_terms;
  }

}
