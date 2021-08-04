<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Cache\Cache;

/**
 * Service provides helper functions for the rcs category taxonomy.
 */
class AlshayaRcsCategoryHelper {

  const VOCABULARY_ID = 'rcs_category';

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
   * Drupal Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Rcs category term cache tags.
   *
   * @var array
   */
  protected $termCacheTags = [];

  /**
   * Constructs a new AlshayaRcsCategoryHelper instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupal Renderer.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              RendererInterface $renderer,
                              LanguageManagerInterface $language_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->languageManager = $language_manager;
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

    // Return if none available.
    if (empty($terms)) {
      return NULL;
    }

    $this->termCacheTags = [
      'taxonomy_term:' . self::VOCABULARY_ID,
      'taxonomy_term:' . self::VOCABULARY_ID . ':new',
    ];

    $data = [];
    foreach ($terms as $term_id) {
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
      ];

      // Get overridden target link.
      $field_target_link_uri = $term->get('field_target_link')->getString();
      if ($field_target_link_uri) {
        $path = UrlHelper::isExternal($field_target_link_uri)
          ? $field_target_link_uri
          : Url::fromUri($field_target_link_uri)->toString(TRUE)->getGeneratedUrl();
        $record['path'] = $path;
      }

      // If highlights entities available.
      $main_menu_highlights = $term->field_main_menu_highlight->getValue();
      if (!empty($main_menu_highlights) && $context != 'app') {
        $record['highlight_paragraphs'] = $this->getHighlightParagraph($main_menu_highlights, $langcode);
      }

      // If icon available, only for web.
      $field_icon = $term->get('field_icon')->getValue() ?? [];
      if ($field_icon && $field_icon['0']['target_id'] && $context != 'app') {
        $image = $this->entityTypeManager->getStorage('file')->load($field_icon['0']['target_id']);
        $record['icon'] = [
          'icon_url' => file_url_transform_relative(file_create_url($image->getFileUri())),
        ];
      }

      // Add term object in array for cache dependency.
      $this->termCacheTags = Cache::mergeTags($this->termCacheTags, $term->getCacheTags());

      $menu_item_slug = $term->get('field_category_slug')->getString();
      $data[$menu_item_slug] = $record;
    }

    return $data;
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
      $paragraph = Paragraph::load($paragraph_id);

      // If unable to load paragraph object.
      if (!$paragraph) {
        continue;
      }

      // Get the translation of the paragraph if exists.
      if ($paragraph->hasTranslation($langcode)) {
        // Replace the current paragraph with translated one.
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
   * Return the RCS category term cache tags cache dependency.
   *
   * @return array
   *   Cacheable term objects.
   */
  public function getTermsCacheTags() {
    return $this->termCacheTags;
  }

}
