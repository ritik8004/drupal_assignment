<?php

namespace Drupal\alshaya_acm_product_category;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Database\Connection;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class ProductCategoryTree.
 */
class ProductCategoryTree implements ProductCategoryTreeInterface {

  const CACHE_BIN = 'alshaya';

  const CACHE_ID = 'product_category_tree';

  const VOCABULARY_ID = 'acq_product_category';

  const CACHE_TAG = 'acq_product_category_list';

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Node storage object.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Cache Backend service for alshaya.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Highlight paragraph ids for all terms.
   *
   * @var array
   */
  protected $highlightParagraphs = [];

  /**
   * Background color for all terms.
   *
   * @var array
   */
  protected $termsBackgroundColor = [];

  /**
   * Font color for all terms.
   *
   * @var array
   */
  protected $termsFontColor = [];

  /**
   * File storage object.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * All terms image and image text font/bg color.
   *
   * @var array
   */
  protected $termsImagesAndColors = [];

  /**
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    CacheBackendInterface $cache,
    RouteMatchInterface $route_match,
    Connection $connection) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->languageManager = $language_manager;
    $this->cache = $cache;
    $this->routeMatch = $route_match;
    $this->connection = $connection;
    $this->fileStorage = $entity_type_manager->getStorage('file');
  }

  /**
   * Get the term tree for 'product_category' vocabulary from cache or fresh.
   *
   * @param int $parent_id
   *   The term parent id, default 0.
   *
   * @return array
   *   Processed term data from cache if available or fresh.
   */
  public function getCategoryTreeCached($parent_id = 0) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $cid = self::CACHE_ID . '_' . $langcode . '_' . $parent_id;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    $term_data = $this->getCategoryTree($langcode, $parent_id);

    $cache_tags = [
      self::CACHE_TAG,
      'node_type:advanced_page',
    ];

    $this->cache->set($cid, $term_data, Cache::PERMANENT, $cache_tags);

    return $term_data;
  }

  /**
   * Get the term tree for 'product_category' vocabulary.
   *
   * Optionally with highlight paragraphs and child.
   *
   * @param string $langcode
   *   Language code in which we need term to be displayed.
   * @param int $parent_tid
   *   Parent term id.
   * @param bool $highlight_paragraph
   *   True if include highlight paragraph else false.
   * @param bool $child
   *   True if include child else false.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryTree($langcode, $parent_tid = 0, $highlight_paragraph = TRUE, $child = TRUE) {
    $data = [];

    // Get all child terms for the given parent.
    $terms = $this->allChildTerms($langcode, $parent_tid, FALSE);

    // Initialize the background color for term.
    $this->termsBackgroundColor = $this->getTermsColors($langcode, 'background');

    // Initialize the font color for the term.
    $this->termsFontColor = $this->getTermsColors($langcode, 'font');

    // Initialize the image and image text font/bg color.
    $this->termsImagesAndColors = $this->getTermsImageAndColor($langcode);

    if (empty($terms)) {
      return [];
    }

    foreach ($terms as $term) {
      $data[$term->tid] = [
        'label' => $term->name,
        'description' => [
          '#markup' => $term->description__value,
        ],
        'id' => $term->tid,
        'path' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString(),
        'active_class' => '',
      ];

      if ($highlight_paragraph) {
        $data[$term->tid]['highlight_paragraph'] = $this->getHighlightParagraph($term->tid, $langcode, self::VOCABULARY_ID);
      }

      if ($child) {
        $data[$term->tid]['child'] = $this->getCategoryTree($langcode, $term->tid);
      }

      // Set the background/highlight color for the term.
      if (!empty($this->termsBackgroundColor[$term->tid])) {
        $data[$term->tid]['term_bg_color'] = $this->termsBackgroundColor[$term->tid];
      }

      // Set the font color for the term.
      if (!empty($this->termsFontColor[$term->tid])) {
        $data[$term->tid]['term_font_color'] = $this->termsFontColor[$term->tid];
      }

      // Set the term image and image text font/bg color.
      if (!empty($this->termsImagesAndColors[$term->tid])) {
        $data[$term->tid]['term_image'] = $this->termsImagesAndColors[$term->tid];
      }

    }

    return $data;
  }

  /**
   * Get highlight paragraph for a term.
   *
   * @param int $tid
   *   Term id.
   * @param string $langcode
   *   Language code.
   * @param string $vid
   *   Vocabulary id.
   *
   * @return array
   *   Highlight paragraphs array.
   */
  protected function getHighlightParagraph($tid, $langcode, $vid) {
    $highlight_paragraphs = [];

    // We fetch this from first request and shouldn't be empty. If empty,
    // assuming its first request and prepare data.
    if (empty($this->highlightParagraphs)) {
      $this->getHighLightParagraphs($vid);
    }

    // If no data in paragraph referenced field.
    if (empty($this->highlightParagraphs[$tid])) {
      return $highlight_paragraphs;
    }

    foreach ($this->highlightParagraphs[$tid] as $paragraph_id) {
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
          $url = Url::fromUri($image_link[0]['uri']);
          $highlight_paragraphs[] = [
            'image_link' => $url->toString(),
            'img' => $renderable_image,
            'title' => $title,
            'description' => $subtitle,
            'highlight_type' => $highlight_type,
            'paragraph_type' => $paragraph_type,
          ];
        }
      }
    }

    return $highlight_paragraphs;
  }

  /**
   * Get the term object from current route.
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   *   Return the taxonomy term object if found else NULL.
   */
  public function getCategoryTermFromRoute() {
    $route_name = $this->routeMatch->getRouteName();
    $term = NULL;
    // If /taxonomy/term/tid page.
    if ($route_name == 'entity.taxonomy_term.canonical') {
      /* @var \Drupal\taxonomy\TermInterface $route_parameter_value */
      $term = $this->routeMatch->getParameter('taxonomy_term');
    }
    // If it's a department page.
    elseif ($route_name == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      $terms = [];
      if ($node->bundle() == 'acq_product') {
        $terms = $node->get('field_category')->getValue();
      }
      elseif ($node->bundle() == 'advanced_page') {
        $terms = $node->get('field_product_category')->getValue();
      }

      if (count($terms) > 0) {
        $term = $this->termStorage->load($terms[0]['target_id']);
      }
    }

    // If term is of 'acq_product_category' vocabulary.
    if ($term instanceof TermInterface && $term->getVocabularyId() == self::VOCABULARY_ID) {
      return $term;
    }

    return $term;
  }

  /**
   * Get all the parents from given term object.
   *
   * @param object $term
   *   The term object.
   *
   * @return array|\Drupal\taxonomy\TermInterface[]
   *   Returns the array of all parents.
   */
  public function getCategoryTermParents($term) {
    $parents = [];
    // If term is of 'acq_product_category' vocabulary.
    if ($term instanceof TermInterface && $term->bundle() == self::VOCABULARY_ID) {
      // Get all parents of the given term.
      $parents = $this->termStorage->loadAllParents($term->id());
    }
    return $parents;
  }

  /**
   * Get all child terms of a given term in a language.
   *
   * @param string $langcode
   *   Language code.
   * @param int $parent_tid
   *   Parent term id.
   * @param bool $exclude_not_in_menu
   *   (optional) If the result should contain items excluded from menu.
   * @param bool $mobile_only
   *   (optional) If the result should have items only for mobile.
   * @param string $vid
   *   (optional) Vocabulary id.
   *
   * @return array
   *   Child term array.
   */
  public function allChildTerms($langcode, $parent_tid, $exclude_not_in_menu = TRUE, $mobile_only = FALSE, $vid = NULL) {
    $vid = empty($vid) ? self::VOCABULARY_ID : $vid;
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid', 'name', 'description__value']);
    $query->innerJoin('taxonomy_term_hierarchy', 'tth', 'tth.tid = tfd.tid');
    $query->innerJoin('taxonomy_term__field_category_include_menu', 'ttim', 'ttim.entity_id = tfd.tid AND ttim.langcode = tfd.langcode');
    $query->innerJoin('taxonomy_term__field_commerce_status', 'ttcs', 'ttcs.entity_id = tfd.tid AND ttcs.langcode = tfd.langcode');
    if ($exclude_not_in_menu) {
      $query->condition('ttim.field_category_include_menu_value', 1);
    }
    if ($mobile_only) {
      $query->innerJoin('taxonomy_term__field_category_quicklink_plp_mob', 'ttmo', 'ttmo.entity_id = tfd.tid AND ttmo.langcode = tfd.langcode');
      $query->condition('ttmo.field_category_quicklink_plp_mob_value', 1);
    }
    $query->condition('ttcs.field_commerce_status_value', 1);
    $query->condition('tth.parent', $parent_tid);
    $query->condition('tfd.langcode', $langcode);
    $query->condition('tfd.vid', $vid);
    $query->orderBy('tfd.weight', 'ASC');
    return $query->execute()->fetchAll();
  }

  /**
   * Get highlight paragraph id for all terms.
   *
   * @param string $vid
   *   Vocabulary id.
   */
  protected function getHighLightParagraphs($vid) {
    $query = $this->connection->select('taxonomy_term__field_main_menu_highlight', 'tmmh');
    $query->fields('tmmh', ['entity_id', 'field_main_menu_highlight_target_id']);
    $query->condition('tmmh.bundle', $vid);
    $highlight_paragraphs = $query->execute()->fetchAll();
    if (!empty($highlight_paragraphs)) {
      foreach ($highlight_paragraphs as $highlight_paragraph) {
        $this->highlightParagraphs[$highlight_paragraph->entity_id][] = $highlight_paragraph->field_main_menu_highlight_target_id;
      }
    }
  }

  /**
   * Gets the colors for all the terms in 'acq_product_category' vocabulary.
   *
   * @param string $langcode
   *   Language code.
   * @param string $type
   *   Color type background/font.
   * @param string $vid
   *   (optional) Vocabulary id.
   *
   * @return array
   *   Array of colors keyed by term id.
   */
  protected function getTermsColors($langcode, $type, $vid = NULL) {
    $vid = empty($vid) ? self::VOCABULARY_ID : $vid;
    $query = $this->connection->select('taxonomy_term__field_term_' . $type . '_color', 'ttbc');
    $query->fields('ttbc', ['entity_id', 'field_term_' . $type . '_color_value']);
    $query->condition('ttbc.langcode', $langcode);
    $query->condition('ttbc.bundle', $vid);
    return $query->execute()->fetchAllKeyed();
  }

  /**
   * Fetch a flat list of all child tids for a given term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Parent term for which child tids need to be fetched.
   * @param array $child_tids
   *   Children tids that needs to be returned by the recursive function.
   *
   * @return array
   *   Flat array of all children terms n-level.
   */
  public function getNestedChildrenTids(TermInterface $term, array &$child_tids = []) {

    // Process further only if the term has children.
    if (count($child_terms = $this->allChildTerms($term->language()->getId(),
        $term->id(), FALSE)) > 0) {

      // Push child term ids into an array & re-lookup for the child if it has
      // further children.
      foreach ($child_terms as $child_term) {
        $child_tids[] = $child_term->tid;
        $child_term_obj = $this->termStorage->load($child_term->tid);
        $this->getNestedChildrenTids($child_term_obj, $child_tids);
      }
    }

    return array_unique($child_tids);
  }

  /**
   * Get the all terms images and image text font/bg color.
   *
   * @param string $langcode
   *   Language code.
   * @param string $vid
   *   (optional) Vocabulary name.
   *
   * @return array
   *   Array of term images and image text color.
   */
  protected function getTermsImageAndColor($langcode, $vid = NULL) {
    $vid = empty($vid) ? self::VOCABULARY_ID : $vid;
    $query = $this->connection->select('taxonomy_term__field_category_image', 'ci');
    $query->fields('ci', [
      'field_category_image_target_id',
      'field_category_image_alt',
      'entity_id',
    ]);
    $query->leftJoin('taxonomy_term__field_image_text_bg_color', 'bg', 'ci.entity_id=bg.entity_id');
    $query->fields('bg', ['field_image_text_bg_color_value']);
    $query->leftJoin('taxonomy_term__field_image_text_font_color', 'fc', 'ci.entity_id=fc.entity_id');
    $query->fields('fc', ['field_image_text_font_color_value']);
    $query->condition('ci.langcode', $langcode);
    $query->condition('fc.langcode', $langcode);
    $query->condition('bg.langcode', $langcode);
    $query->condition('ci.bundle', $vid);
    $results = $query->execute()->fetchAll();

    // If there are results.
    if (!empty($results)) {
      $data = [];
      foreach ($results as $result) {
        // If image is available, only then, we process the result.
        if (!empty($image = $this->fileStorage->load($result->field_category_image_target_id))) {
          $data[$result->entity_id] = [
            'bg_color' => $result->field_image_text_bg_color_value ? $result->field_image_text_bg_color_value : NULL,
            'font_color' => $result->field_image_text_font_color_value ? $result->field_image_text_font_color_value : NULL,
            'term_image' => [
              '#theme' => 'image_style',
              '#style_name' => '186x216',
              '#uri' => $image->getFileUri(),
              '#alt' => $result->field_category_image_alt ? $result->field_category_image_alt : '',
            ],
          ];
        }
      }

      return $data;
    }

    return [];
  }

}
