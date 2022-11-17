<?php

namespace Drupal\alshaya_rcs_main_menu\EventSubscriber;

use Drupal\rest\ResourceResponse;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper;
use Drupal\alshaya_acm_product\AlshayaRequestContextManager;
use Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides enriched rcs categories data.
 */
class AlshayaRcsCategoriesEnrichmentEventSubscriber implements EventSubscriberInterface {

  public const VOCABULARY_ID = 'rcs_category';

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The alshaya rcs_category helper.
   *
   * @var \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper
   */
  protected $alshayaRcsCategoryHelper;

  /**
   * Alshaya Request Context Manager.
   *
   * @var \Drupal\alshaya_acm_product\AlshayaRequestContextManager
   */
  protected $requestContextManager;

  /**
   * Drupal Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Cacheability metadata.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $cacheabilityMetadata;

  /**
   * AlshayaRcsCategoryResource constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\alshaya_rcs_main_menu\Service\AlshayaRcsCategoryHelper $alshaya_rcs_category_helper
   *   The alshaya rcs_category helper.
   * @param \Drupal\alshaya_acm_product\AlshayaRequestContextManager $alshaya_request_context_manager
   *   Alshaya Request Context Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   */
  public function __construct(
    RendererInterface $renderer,
    LanguageManagerInterface $language_manager,
    AlshayaRcsCategoryHelper $alshaya_rcs_category_helper,
    AlshayaRequestContextManager $alshaya_request_context_manager,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher
  ) {
    $this->renderer = $renderer;
    $this->languageManager = $language_manager;
    $this->alshayaRcsCategoryHelper = $alshaya_rcs_category_helper;
    $this->requestContextManager = $alshaya_request_context_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $event_dispatcher;
    $this->cacheabilityMetadata = new CacheableMetadata();
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      GetEnrichedCategoryDataEvent::EVENT_NAME => [
        // This should execute before the subscriber in
        // alshaya_mobile_app module.
        ['onGetEnrichedCategoryData', 2],
      ],
    ];
  }

  /**
   * Subscriber for providing enriched category data.
   *
   * @param \Drupal\alshaya_acm_product_category\Event\GetEnrichedCategoryDataEvent $event
   *   The event data.
   */
  public function onGetEnrichedCategoryData(GetEnrichedCategoryDataEvent $event) {
    // Pass context for filtering a few fields.
    $context = $this->requestContextManager->getContext();

    $term_data = $this->getRcsCategoryEnrichmentData(
      $event->getLangcode(),
      $context
    );

    $event->setData($term_data);
    $event->setCacheabilityMetadata($this->cacheabilityMetadata);
  }

  /**
   * Get the placeholder term data from rcs_category.
   *
   * @param string $langcode
   *   Language code to get terms.
   * @param string $context
   *   Context value either web/app.
   *
   * @return array
   *   Placeholder term's data.
   */
  public function getRcsCategoryEnrichmentData($langcode, $context) {
    // Get the placeholder term from config.
    $config = $this->configFactory->get('rcs_placeholders.settings');
    $entity_id = $config->get('category.placeholder_tid');

    // Get all the terms from rcs_category taxonomy.
    $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
    $query->condition('vid', self::VOCABULARY_ID);
    $query->condition('tid', $entity_id, '<>');
    $query->condition('langcode', $langcode);
    $terms = $query->execute();

    $this->cacheabilityMetadata->addCacheableDependency($config);
    $this->cacheabilityMetadata->addCacheTags([
      'taxonomy_term:' . self::VOCABULARY_ID,
      'taxonomy_term_list:' . self::VOCABULARY_ID,
    ]);

    // Return if none available.
    if (empty($terms)) {
      return [];
    }

    $data = [];
    foreach ($terms as $term_id) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);

      // Skip if category slug field is empty.
      if (empty($term->field_category_slug->value)) {
        continue;
      }

      // Get the translation of the term if exists.
      if ($term->hasTranslation($langcode)) {
        // Replace the current term with translated one.
        $term = $term->getTranslation($langcode);
      }

      $record = [
        'id' => $term_id,
        'name' => $term->label(),
        'include_in_desktop' => (int) $term->get('field_include_in_desktop')->getString(),
        'include_in_mobile_tablet' => (int) $term->get('field_include_in_mobile_tablet')->getString(),
        'move_to_right' => (int) $term->get('field_move_to_right')->getString(),
        'font_color' => $term->get('field_term_font_color')->getString(),
        'background_color' => $term->get('field_term_background_color')->getString(),
        'remove_from_breadcrumb' => (int) $term->get('field_remove_term_in_breadcrumb')->getString(),
        'item_clickable' => (bool) $term->get('field_display_as_clickable_link')->getString(),
      ];

      // Get overridden target link.
      $field_target_link_uri = $term->get('field_target_link')->getString();
      // Get target link only if the override target link checkbox is checked.
      if ($term->get('field_override_target_link')->getString() && $field_target_link_uri) {
        $path = UrlHelper::isExternal($field_target_link_uri)
          ? $field_target_link_uri
          : Url::fromUri($field_target_link_uri)->toString(TRUE)->getGeneratedUrl();
        // Remove langcode prefix if it exists as that will be added via FE.
        $path = preg_replace('/^\/' . $this->languageManager->getCurrentLanguage()->getId() . '\//', '', $path);
        $record['url_path'] = $path;
      }

      // If highlights entities available.
      $main_menu_highlights = $term->field_main_menu_highlight->getValue();
      if (!empty($main_menu_highlights) && $context != 'app') {
        $record['highlight_paragraphs'] = $this->getHighlightParagraph($main_menu_highlights, $langcode);
      }

      // List of all the images that are enriched.
      $images = [
        'field_icon' => [
          'key' => 'icon_url',
          'app' => FALSE,
        ],
        'field_logo_active_image' => [
          'key' => 'logo_active_image',
        ],
        'field_logo_header_image' => [
          'key' => 'logo_header_image',
        ],
        'field_logo_inactive_image' => [
          'key' => 'logo_inactive_image',
        ],
      ];

      foreach ($images as $key => $value) {
        if ($term->hasField($key)) {
          // If icon available, only for web.
          if (array_key_exists('app', $value) && $context == 'app') {
            continue;
          }
          $image_url = $this->getImageFromField($key, $term);
          if ($image_url) {
            $record['icon'][$value['key']] = $image_url;
          }
        }
      }

      $menu_item_slug = $term->get('field_category_slug')->getString();

      // Add term object in array for cache dependency.
      $this->cacheabilityMetadata->addCacheableDependency($term);

      $data[$menu_item_slug] = $record;
    }

    return $data;
  }

  /**
   * Extract image from the term image field.
   *
   * @param string $field
   *   The field key string.
   * @param \Drupal\taxonomy\Entity\TermInterface $term
   *   The term object.
   *
   * @return null|string
   *   The relative URL of the image.
   */
  protected function getImageFromField(string $field, TermInterface $term) {
    $field_image = $term->get($field)->getValue() ?? [];
    if ($field_image && $field_image[0]['target_id']) {
      /** @var \Drupal\file\FileInterface $image */
      $image = $this->entityTypeManager->getStorage('file')->load($field_image['0']['target_id']);
      // Return the image relative URL.
      return file_url_transform_relative(file_create_url($image->getFileUri()));
    }

    return NULL;
  }

  /**
   * Get highlight paragraph for a term.
   *
   * @param array $highlights
   *   Paragraphs Ids.
   * @param string $langcode
   *   Language code.
   *
   * @return array
   *   Highlight paragraphs array.
   */
  protected function getHighlightParagraph(array $highlights, $langcode) {
    $language = $this->languageManager->getLanguage($langcode);
    $uri_options = ['language' => $language];

    $highlight_paragraphs = [];
    $text_link_para = FALSE;

    foreach ($highlights as $highlight) {
      $paragraph_id = $highlight['target_id'];

      // Load paragraph entity.
      /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
      $paragraph = Paragraph::load($paragraph_id);

      // If unable to load paragraph object.
      if (!$paragraph) {
        continue;
      }

      // Get the translation of the paragraph if exists.
      if ($paragraph->hasTranslation($langcode)) {
        // Replace the current paragraph with translated one.
        /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
        $paragraph = $paragraph->getTranslation($langcode);
      }

      if ($paragraph && $paragraph->getType() == 'main_menu_highlight' && !empty($paragraph->get('field_highlight_image'))) {
        $image = $paragraph->get('field_highlight_image')->getValue();
        $image_link = $paragraph->get('field_highlight_link')->getValue();
        $title = $paragraph->get('field_highlight_title')->getString();
        $subtitle = $paragraph->get('field_highlight_subtitle')->getString();
        $highlight_type = (empty($title) && empty($subtitle)) ? 'promo_block' : ((!empty($title) && !empty($subtitle)) ? 'title_subtitle' : 'highlight');
        $renderable_image = $paragraph->get('field_highlight_image')
          ->view('default');
        $paragraph_type = $paragraph->getType();
        if (!empty($image)) {
          $url = Url::fromUri($image_link[0]['uri'], $uri_options);
          $highlight_paragraphs[] = [
            'image_link' => $url->toString(TRUE)->getGeneratedUrl(),
            'img' => $renderable_image,
            'title' => $title,
            'description' => $subtitle,
            'highlight_type' => $highlight_type,
            'paragraph_type' => $paragraph_type,
          ];
        }
      }

      // If 'text_link' paragraph.
      if ($paragraph && $paragraph->getType() == 'text_links') {
        // Get heading link.
        $heading_link = $paragraph->get('field_heading_link')->getValue();
        // If heading link available, only then we render.
        if (!empty($heading_link)) {
          $subheading_links = [];
          if (!empty($sub_heading_links = $paragraph->get('field_sub_link')->getValue())) {
            // Filter/Remove empty items(uri).
            $sub_heading_links = array_filter($sub_heading_links, 'array_filter');
            foreach ($sub_heading_links as $sublink) {
              $subheading_links[] = [
                'subheading_link_uri' => $sublink['uri'],
                'subheading_link_title' => $sublink['title'],
                'link' => $sublink['uri'] == 'internal:#' ? '' : Url::fromUri($sublink['uri'], $uri_options),
              ];
            }
          }

          $highlight_paragraphs[] = [
            'heading_link_uri' => $heading_link[0]['uri'],
            'heading_link_title' => $heading_link[0]['title'],
            'link' => $heading_link[0]['uri'] == 'internal:#' ? '' : Url::fromUri($heading_link[0]['uri'], $uri_options),
            'subheading' => $subheading_links,
            'paragraph_type' => $paragraph->getType(),
          ];

          $text_link_para = TRUE;
        }
      }
    }

    if (!empty($highlight_paragraphs)) {
      $build = [
        '#theme' => 'alshaya_main_menu_highlights',
        '#data' => [
          'highlight_paragraph' => $highlight_paragraphs,
        ],
      ];

      return [
        'markup' => $this->renderer->renderRoot($build),
        'text_link_para' => $text_link_para,
      ];
    }

    return $highlight_paragraphs;
  }

  /**
   * Adding rcs category terms dependency to response.
   *
   * @param \Drupal\rest\ResourceResponse $response
   *   Response object.
   */
  protected function addCacheableTermDependency(ResourceResponse $response) {
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => $this->alshayaRcsCategoryHelper->getTermsCacheTags(),
      ],
    ]));
  }

}
