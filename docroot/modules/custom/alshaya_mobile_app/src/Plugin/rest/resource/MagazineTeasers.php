<?php

namespace Drupal\alshaya_mobile_app\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\alshaya_mobile_app\Service\MobileAppUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\rest\ResourceResponse;
use Drupal\views\Views;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;

/**
 * Provides a resource to get magazine teasers.
 *
 * @RestResource(
 *   id = "magazine_teasers",
 *   label = @Translation("Magazine Teasers"),
 *   uri_paths = {
 *     "canonical" = "/rest/v1/page/magazine-teasers"
 *   }
 * )
 */
class MagazineTeasers extends ResourceBase {

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
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   Current time service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    MobileAppUtility $mobile_app_utility,
    RequestStack $request_stack,
    LanguageManagerInterface $language_manager,
    EntityRepositoryInterface $entity_repository,
    DateFormatterInterface $date_formatter,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->mobileAppUtility = $mobile_app_utility;
    $this->requestStack = $request_stack->getCurrentRequest();
    $this->languageManager = $language_manager;
    $this->currentLanguage = $this->languageManager->getCurrentLanguage()->getId();
    $this->entityRepository = $entity_repository;
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
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
      $container->get('language_manager'),
      $container->get('entity.repository'),
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response returns the magazine listing page.
   */
  public function get() {
    $entityCacheTags = [];
    $entityCacheContexts = ['url'];
    $offset = $this->requestStack->query->get('offset');
    $limit = $this->requestStack->query->get('limit');
    if ($offset == NULL || $limit == NULL) {
      return $this->mobileAppUtility->throwException();
    }
    $response_data = [];

    $context = new RenderContext();
    $result = $this->renderer->executeInRenderContext($context, function () {
      $mag_page_view = Views::getView('magazine_articles');
      $mag_page_view->execute('list');
      $mag_page_view_total_count = $mag_page_view->total_rows;

      $magazine_listing_page = Views::getView('magazine_articles');
      $magazine_listing_page->setDisplay('list');
      $magazine_view_title = $magazine_listing_page->getTitle();
      $magazine_listing_page->setOffset($this->requestStack->query->get('offset'));
      $magazine_listing_page->setItemsPerPage($this->requestStack->query->get('limit'));
      $magazine_listing_page->execute();
      $magazine_listing_page_result = $magazine_listing_page->result;
      $magazine_listing_current_page_count = (int) (is_countable($magazine_listing_page_result) ? count($magazine_listing_page_result) : 0);

      return [
        'mag_page_view_total_count' => $mag_page_view_total_count,
        'magazine_view_title' => $magazine_view_title,
        'magazine_listing_page_result' => $magazine_listing_page_result,
        'magazine_listing_current_page_count' => $magazine_listing_current_page_count,
      ];
    });
    // Handle any bubbled cacheability metadata.
    if (!$context->isEmpty()) {
      $bubbleable_metadata = $context->pop();
      BubbleableMetadata::createFromObject($result)
        ->merge($bubbleable_metadata);
    }

    if (isset($result['magazine_listing_current_page_count']) && $result['magazine_listing_current_page_count'] > 0) {
      $array_response_data = [
        'type' => 'magazine_listing_page',
        'view_title' => $result['magazine_view_title'],
        'total_view_count' => (int) $result['mag_page_view_total_count'],
        'current_page_count' => $result['magazine_listing_current_page_count'],
      ];
      foreach ($result['magazine_listing_page_result'] as $value) {
        $entity = $this->entityRepository->getTranslationFromContext($value->_entity);
        $magazine_entity_url_obj = Url::fromRoute('entity.node.canonical', ['node' => $entity->id()]);
        $magazine_entity_url = $magazine_entity_url_obj->toString(TRUE);
        $response_data['url'] = $magazine_entity_url->getGeneratedUrl();
        $response_data['deeplink'] = $this->mobileAppUtility->getDeepLink($entity);
        $response_data['title'] = $entity->getTitle();
        if (!empty($entity->field_magazine_date->getValue())) {
          $magazine_date = $entity->field_magazine_date->getValue()[0]['value'];
          $response_data['date'] = $this->dateFormatter->format(strtotime($magazine_date), 'magazine_date', '', NULL, $this->currentLanguage);
        }
        if ($entity->get('field_magazine_homepage_image')->getValue()) {
          $response_data['image'] = $this->mobileAppUtility->getImages($entity, 'field_magazine_homepage_image', 'magazine_article_listing');
        }
        if ($entity->hasField('field_magazine_category') && !empty($entity->field_magazine_category)) {
          $magazine_category_entity = $entity->get('field_magazine_category')->referencedEntities();
          foreach ($magazine_category_entity as $magazine_category_val) {
            $magazine_category = $this->entityRepository->getTranslationFromContext($magazine_category_val);
            $magazine_category_data['id'] = (int) $magazine_category->id();
            $magazine_category_data['name'] = $magazine_category->getName();
            $response_data['magazine_category'] = $magazine_category_data;
            $entityCacheTags = array_merge($entityCacheTags, $magazine_category->getCacheTags());
            $entityCacheContexts = array_merge($entityCacheContexts, $magazine_category->getCacheContexts());
          }
        }
        if (!empty($entity->field_magazine_slogan->getValue())) {
          $response_data['slogan'] = $entity->field_magazine_slogan->getValue()[0]['value'];
        }
        $response_data['node_more_link_text'] = $this->t('read the story');
        $array_response_data['items'][] = $response_data;
        $entityCacheTags = array_merge($entityCacheTags, $entity->getCacheTags());
        $entityCacheContexts = array_merge($entityCacheContexts, $entity->getCacheContexts());
      }
    }
    else {
      $array_response_data['message'] = $this->t('Data not found');
    }

    $response = new ResourceResponse($array_response_data);
    $cacheableMetadata = $response->getCacheableMetadata();
    $cacheableMetadata->addCacheTags($entityCacheTags);
    $cacheableMetadata->addCacheContexts($entityCacheContexts);
    $response->addCacheableDependency($cacheableMetadata);

    return $response;
  }

}
