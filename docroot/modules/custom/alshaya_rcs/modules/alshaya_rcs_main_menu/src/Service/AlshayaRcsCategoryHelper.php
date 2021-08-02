<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;

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
   * Rcs category cacheable term objects list.
   *
   * @var array
   */
  protected $cacheableTerms = [];

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
    $terms = $query->execute();

    // Return if none available.
    if (empty($terms)) {
      return NULL;
    }

    $data = [];
    foreach ($terms as $term_id) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($term_id);

      // Skip if category slug field is empty.
      if (empty($term->field_category_slug->value)) {
        continue;
      }

      $record = [
        'id' => $term_id,
        'name' => $term->label(),
        'include_in_desktop' => (int) $term->field_include_in_desktop->value,
        'include_in_mobile_tablet' => (int) $term->field_include_in_mobile_tablet->value,
        'move_to_right' => (int) $term->field_move_to_right->value,
        'font_color' => $term->field_term_font_color->value,
        'background_color' => $term->field_term_background_color->value,
      ];

      if ($term->field_target_link->uri) {
        $path = UrlHelper::isExternal($term->field_target_link->uri)
          ? $term->field_target_link->uri
          : Url::fromUri($term->field_target_link->uri)->toString(TRUE)->getGeneratedUrl();
        $record['path'] = $path;
      }

      // If highlights entities available.
      $main_menu_highlights = $term->field_main_menu_highlight->getValue();
      if (!empty($main_menu_highlights) && $context != 'app') {
        $record['highlight_paragraphs'] = $this->getHighlightParagraph($main_menu_highlights, $langcode);
      }

      // Add term object in array for cache dependency.
      $this->cacheableTerms[] = $term;

      $data[$term->field_category_slug->value] = $record;
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
   * Return the RCS category term array for cache dependency.
   *
   * @return array
   *   Cacheable term objects.
   */
  public function getCacheableTerms() {
    return $this->cacheableTerms;
  }

}
