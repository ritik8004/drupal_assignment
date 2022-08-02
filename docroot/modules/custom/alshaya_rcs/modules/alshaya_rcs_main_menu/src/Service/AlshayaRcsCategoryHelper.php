<?php

namespace Drupal\alshaya_rcs_main_menu\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Service provides helper functions for the rcs category taxonomy.
 */
class AlshayaRcsCategoryHelper {

  public const VOCABULARY_ID = 'rcs_category';

  /**
   * Prefix used for the endpoint.
   */
  public const ENDPOINT_PREFIX_V1 = '/rest/v1/';

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
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Cache Backend object for "cache.data".
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Database connection.
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
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache backend object.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              ConfigFactoryInterface $config_factory,
                              RendererInterface $renderer,
                              LanguageManagerInterface $language_manager,
                              AliasManagerInterface $alias_manager,
                              CacheBackendInterface $cache,
                              Connection $connection,
                              ModuleHandlerInterface $module_handler
                              ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->cache = $cache;
    $this->connection = $connection;
    $this->moduleHandler = $module_handler;
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

    $this->termCacheTags = [
      'taxonomy_term:' . self::VOCABULARY_ID,
      'taxonomy_term_list:' . self::VOCABULARY_ID,
    ];

    // Return if none available.
    if (empty($terms)) {
      return [];
    }

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
        'remove_from_breadcrumb' => (int) $term->get('field_remove_term_in_breadcrumb')->getString(),
        'item_clickable' => (bool) $term->get('field_display_as_clickable_link')->getString(),
        'deeplink' => $this->getDeepLink($term),
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

      // Add term object in array for cache dependency.
      $this->termCacheTags = Cache::mergeTags($this->termCacheTags, $term->getCacheTags());

      $menu_item_slug = $term->get('field_category_slug')->getString();
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
   *   Term cache tags.
   */
  public function getTermsCacheTags() {
    return $this->termCacheTags;
  }

  /**
   * Get Deep link based on give object.
   *
   * @param object $object
   *   Object of term containing term data.
   *
   * @return string
   *   Return deeplink url.
   */
  public function getDeepLink($object) {
    $slug = $object->get('field_category_slug')->getString();
    // Get all the departments pages having category slug value.
    $department_pages = $this->getDepartmentPages();
    // @todo Change the logic here once we get the prefixed response from
    // magento.
    if (array_key_exists($slug, $department_pages)) {
      return self::ENDPOINT_PREFIX_V1 . 'page/advanced?url=' .
      ltrim(
        $this->aliasManager->getAliasByPath(
          '/node/' . $department_pages[$slug],
          $this->languageManager->getCurrentLanguage()->getId(),
        ),
        '/'
      );
    }

    return '';
  }

  /**
   * Check for given path, department page exists.
   *
   * Check for given path, department page exists. If department page exists
   * then return that department page node id or return false.
   *
   * @param string $path
   *   The current route path.
   *
   * @return int|bool
   *   Department page node id or false.
   */
  public function isDepartmentPage(string $path) {
    $data = [];
    // Check for cache first.
    $cache = $this->cache->get('alshaya_rcs_main_menu:slug:nodes');
    if ($cache) {
      $data = $cache->data;
      // If cache hit.
      if (!empty($data[$path])) {
        return $data[$path];
      }
    }

    // Get all department pages.
    $department_pages = $this->getDepartmentPages();
    // If there is department page available for given term.
    if (isset($department_pages[$path])) {
      $nid = $department_pages[$path];
      /** @var \Drupal\node\Entity\Node $node */
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if (is_object($node)) {
        if ($node->isPublished()) {
          $data[$path] = $nid;
          $this->cache->set('alshaya_rcs_main_menu:slug:nodes', $data, Cache::PERMANENT, $node->getCacheTags());
          return $nid;
        }
      }
    }

    return FALSE;
  }

  /**
   * Helper function to fetch list of department pages.
   *
   * @return array
   *   Nids of department pages keyed by slug.
   */
  public function getDepartmentPages() {
    static $department_pages = [];

    // We cache the nid-tid relationship for a single page request.
    if (empty($department_pages)) {
      $query = $this->connection->select('node__field_category_slug', 'nfcs');
      $query->addField('nfcs', 'field_category_slug_value', 'tid');
      $query->addField('nfcs', 'entity_id', 'nid');
      $department_pages = $query->execute()->fetchAllKeyed();
    }

    return $department_pages;
  }

  /**
   * Helper function to build the graphql query dynamically.
   *
   * @param int $depth
   *   Define the depth of the query.
   * @param bool $is_root_level
   *   Checks if depth is at root level.
   *
   * @return string
   *   The graphql query to fetch data using API.
   */
  public function getRcsCategoryMenuQuery($depth = 0, $is_root_level = TRUE) {
    $item_key = $is_root_level ? 'items' : 'children';
    $category_fields = [
      'id',
      'level',
      'name',
      'meta_title',
      'include_in_menu',
      'url_path',
      'url_key',
      'show_on_dpt',
      'show_in_lhn',
      'show_in_app_navigation',
      'position',
      'is_anchor',
      'display_view_all',
    ];

    $this->moduleHandler->alter('alshaya_rcs_category_query_fields', $category_fields);

    $query = [
      $item_key => $category_fields,
    ];
    if ($depth > 0) {
      $query[$item_key] = array_merge($query[$item_key], $this->getRcsCategoryMenuQuery($depth - 1, FALSE));
    }
    return $query;
  }

}
