<?php

namespace Drupal\alshaya_acm_product_category;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\Core\Database\Connection;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\alshaya_acm_product\ProductCategoryHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ProductCategoryTree.
 */
class ProductCategoryTree implements ProductCategoryTreeInterface {

  use LoggerChannelTrait;

  const CACHE_BIN = 'alshaya';

  const CACHE_ID = 'product_category_tree';

  const VOCABULARY_ID = 'acq_product_category';

  const CACHE_TAG = 'taxonomy_term:acq_product_category';

  const PLP_LAYOUT_1 = 'campaign-plp-style-1';

  /**
   * This will be used whether include_in_menu used or not.
   *
   * @var bool
   */
  protected $excludeNotInMenu = TRUE;

  /**
   * Term storage object.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

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
   * Product Category Helper service object.
   *
   * @var \Drupal\alshaya_acm_product\ProductCategoryHelper
   */
  protected $productCategoryHelper;

  /**
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity Repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Route match service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\alshaya_acm_product\ProductCategoryHelper $product_category_helper
   *   Product Category Helper service object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              EntityRepositoryInterface $entity_repository,
                              LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache,
                              RouteMatchInterface $route_match,
                              RequestStack $request_stack,
                              CurrentPathStack $current_path,
                              Connection $connection,
                              ProductCategoryHelper $product_category_helper) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
    $this->cache = $cache;
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
    $this->currentPath = $current_path;
    $this->connection = $connection;
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->productCategoryHelper = $product_category_helper;
  }

  /**
   * Get the term tree for 'product_category' vocabulary from cache or fresh.
   *
   * @param int $parent_id
   *   The term parent id, default 0.
   * @param string $langcode
   *   (optional) The language code.
   * @param bool $reset_cache
   *   (optional) Flag to reset the cache.
   *
   * @return array
   *   Processed term data from cache if available or fresh.
   */
  public function getCategoryTreeCached($parent_id = 0, $langcode = NULL, $reset_cache = FALSE) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = self::CACHE_ID . '_' . $langcode . '_' . $parent_id;

    if (!($reset_cache) && $term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    $term_data = $this->getCategoryTree($langcode, $parent_id);

    // We will invalidate cache for this only from queue.
    $this->cache->set($cid, $term_data);

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

    $exclude_not_in_menu = $this->getExcludeNotInMenu();

    // Get all child terms for the given parent.
    $terms = $this->allChildTerms($langcode, $parent_tid, $exclude_not_in_menu);

    if (empty($terms)) {
      return [];
    }

    // Initialize the background color for term.
    $this->termsBackgroundColor = $this->getTermsColors($langcode, 'background');

    // Initialize the font color for the term.
    $this->termsFontColor = $this->getTermsColors($langcode, 'font');

    // Initialize the image and image text font/bg color.
    $this->termsImagesAndColors = $this->getTermsImageAndColor($langcode);

    foreach ($terms as $term) {
      $data[$term->tid] = [
        'label' => $term->name,
        'description' => [
          '#markup' => $term->description__value,
        ],
        'id' => $term->tid,
        'path' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->tid])->toString(),
        'active_class' => '',
        'class' => [],
        'clickable' => !is_null($term->field_display_as_clickable_link_value) ? $term->field_display_as_clickable_link_value : TRUE,
        'display_in_desktop' => $term->display_in_desktop,
        'display_in_mobile' => $term->display_in_mobile,
        // The actual depth of the term. For super category feature enabled,
        // the depth may be wrong according to main menu. which can be
        // processed when required.
        //
        // @see alshaya_main_menu_alshaya_main_menu_links_alter().
        'depth' => (int) $term->depth_level,
        'lhn' => is_null($term->field_show_in_lhn_value) ? (int) $term->include_in_menu : (int) $term->field_show_in_lhn_value,
        'move_to_right' => !is_null($term->field_move_to_right_value) ? (bool) $term->field_move_to_right_value : FALSE,
        'app_navigation_link' => !is_null($term->field_show_in_app_navigation_value) ? (bool) $term->field_show_in_app_navigation_value : FALSE,
      ];

      if (!$term->display_in_desktop) {
        $data[$term->tid]['class'][] = 'hide-on-desktop';
      }

      if (!$term->display_in_mobile) {
        $data[$term->tid]['class'][] = 'hide-on-mobile';
      }

      if ($term->field_override_target_link_value) {
        $data[$term->tid]['path'] = UrlHelper::isExternal($term->field_target_link_uri) ? $term->field_target_link_uri : Url::fromUri($term->field_target_link_uri)->toString();
        $data[$term->tid]['class'][] = 'overridden-link';
      }

      if (is_object($file = $this->getIcon($term->tid))
          && !empty($file->field_icon_target_id)
      ) {
        $image = $this->fileStorage->load($file->field_icon_target_id);
        $data[$term->tid]['icon'] = [
          'url' => file_create_url($image->getFileUri()),
          'width' => (int) $file->field_icon_width,
          'height' => (int) $file->field_icon_height,
        ];
        $data[$term->tid]['class'][] = 'with-icon';
      }

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
                'link' => $sublink['uri'] == 'internal:#' ? '' : Url::fromUri($sublink['uri']),
              ];
            }
          }

          $highlight_paragraphs[] = [
            'heading_link_uri' => $heading_link[0]['uri'],
            'heading_link_title' => $heading_link[0]['title'],
            'link' => $heading_link[0]['uri'] == 'internal:#' ? '' : Url::fromUri($heading_link[0]['uri']),
            'subheading' => $subheading_links,
            'paragraph_type' => $paragraph->getType(),
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
    static $term = NULL;

    if ($term instanceof TermInterface) {
      return $term;
    }

    $route_name = $this->routeMatch->getRouteName();

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
    elseif (in_array($route_name, ['views.ajax', 'facets.block.ajax'])) {
      $q = NULL;

      // For facets block we get it in current request itself.
      if ($route_name === 'facets.block.ajax') {
        $q = $this->requestStack->getCurrentRequest()->getRequestUri();
      }
      // For views ajax requests it replaces current path.
      // We get it from there.
      else {
        // For some reason we get double forward slash in beginning.
        // We replace it with single forward slash.
        $q = str_replace('//', '/', $this->currentPath->getPath());
      }

      if ($q) {
        $route_params = Url::fromUserInput($q)->getRouteParameters();
        if (isset($route_params['taxonomy_term'])) {
          $term = $this->termStorage->load($route_params['taxonomy_term']);
        }
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
    $query->fields('tfd', ['tid', 'name', 'description__value', 'depth_level'])
      ->fields('ttdcl', ['field_display_as_clickable_link_value'])
      ->fields('target_link', ['field_target_link_uri'])
      ->fields('override_target', ['field_override_target_link_value']);
    $query->addField('ttim', 'field_category_include_menu_value', 'include_in_menu');
    $query->addField('in_desktop', 'field_include_in_desktop_value', 'display_in_desktop');
    $query->addField('in_mobile', 'field_include_in_mobile_tablet_value', 'display_in_mobile');
    $query->innerJoin('taxonomy_term__parent', 'tth', 'tth.entity_id = tfd.tid');
    $query->leftJoin('taxonomy_term__field_display_as_clickable_link', 'ttdcl', 'ttdcl.entity_id = tfd.tid');
    $query->innerJoin('taxonomy_term__field_category_include_menu', 'ttim', 'ttim.entity_id = tfd.tid AND ttim.langcode = tfd.langcode');
    $query->leftJoin('taxonomy_term__field_include_in_desktop', 'in_desktop', 'in_desktop.entity_id = tfd.tid');
    $query->leftJoin('taxonomy_term__field_include_in_mobile_tablet', 'in_mobile', 'in_mobile.entity_id = tfd.tid');
    $query->innerJoin('taxonomy_term__field_commerce_status', 'ttcs', 'ttcs.entity_id = tfd.tid AND ttcs.langcode = tfd.langcode');
    $query->leftJoin('taxonomy_term__field_target_link', 'target_link', 'target_link.entity_id = tfd.tid');
    $query->leftJoin('taxonomy_term__field_override_target_link', 'override_target', 'override_target.entity_id = tfd.tid');
    if ($exclude_not_in_menu) {
      $query->condition('ttim.field_category_include_menu_value', 1);
    }
    if ($mobile_only) {
      $query->innerJoin('taxonomy_term__field_mobile_only_dpt_page_link', 'ttmo', 'ttmo.entity_id = tfd.tid');
      $query->condition('ttmo.field_mobile_only_dpt_page_link_value', 1);
    }

    // For the lhn.
    $query->leftJoin('taxonomy_term__field_show_in_lhn', 'tlhn', 'tlhn.entity_id = tfd.tid');
    $query->fields('tlhn', ['field_show_in_lhn_value']);

    // For the `move to right`.
    $query->leftJoin('taxonomy_term__field_move_to_right', 'mtr', 'mtr.entity_id = tfd.tid');
    $query->fields('mtr', ['field_move_to_right_value']);

    // For the `app navigation links`.
    $query->leftJoin('taxonomy_term__field_show_in_app_navigation', 'mln', 'mln.entity_id = tfd.tid');
    $query->fields('mln', ['field_show_in_app_navigation_value']);

    $query->condition('ttcs.field_commerce_status_value', 1);
    $query->condition('tth.parent_target_id', $parent_tid);
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
   * Gets the image from 'field_promotion_banner' field.
   *
   * @param int $tid
   *   Taxonomy term id.
   * @param string $langcode
   *   Language code.
   *
   * @return object
   *   Object containing fields data.
   */
  public function getBanner($tid, $langcode) {
    $query = $this->connection->select('taxonomy_term__field_promotion_banner', 'ttbc');
    $query->fields('ttbc', [
      'entity_id',
      'field_promotion_banner_target_id',
    ]);
    $query->condition('ttbc.entity_id', $tid);
    $query->condition('ttbc.langcode', $langcode);
    $query->condition('ttbc.bundle', ProductCategoryTree::VOCABULARY_ID);
    return $query->execute()->fetchObject();
  }

  /**
   * Gets the image from 'field_icon' field.
   *
   * @param int $tid
   *   Taxonomy term id.
   *
   * @return object
   *   Object containing fields data.
   */
  public function getIcon($tid) {
    return $this->getImageField($tid, 'field_icon');
  }

  /**
   * Gets the image from 'field_promotion_banner_mobile' field.
   *
   * @param int $tid
   *   Taxonomy term id.
   * @param string $langcode
   *   Language code.
   *
   * @return object
   *   Object containing fields data.
   */
  public function getMobileBanner($tid, $langcode) {
    return $this->getImageField($tid, 'field_promotion_banner_mobile', $langcode);
  }

  /**
   * Get the image table fields for given field and term.
   *
   * @param int $tid
   *   The term id.
   * @param string $field
   *   The field name.
   * @param string|null $langcode
   *   (optional) Language code.
   *
   * @return object|null
   *   Object containing fields data.
   */
  protected function getImageField($tid, $field, $langcode = NULL) {
    $query = $this->connection->select("taxonomy_term__{$field}", 'term_image_field');
    $query->fields('term_image_field', [
      'entity_id',
      "{$field}_target_id",
      "{$field}_width",
      "{$field}_height",
    ]);
    $query->condition('term_image_field.entity_id', $tid);
    $query->condition('term_image_field.bundle', ProductCategoryTree::VOCABULARY_ID);
    if (!empty($langcode)) {
      $query->condition('term_image_field.langcode', $langcode);
    }
    return $query->execute()->fetchObject();
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

  /**
   * Get the innermost term id for a product from current route.
   *
   * @return int|null
   *   Return the taxonomy term ID if found else NULL.
   */
  public function getProductInnermostCategoryIdFromRoute() {
    static $tid = NULL;

    if (empty($tid) && $this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
      $terms = [];
      if ($node->bundle() == 'acq_product') {
        $terms = $node->get('field_category')->getValue();
      }

      if (count($terms) > 0) {
        $tid = $this->productCategoryHelper->termTreeGroup($terms);
      }
    }

    return $tid;
  }

  /**
   * Get the complete category tree.
   *
   * @param mixed $langcode
   *   (Optional) Language code.
   * @param mixed $parent_id
   *   (Optional) Parent id.
   *
   * @return array
   *   Term tree.
   */
  public function getCategoryTreeWithIncludeInMenu($langcode = NULL, $parent_id = 0) {
    // This to not consider `include_in_menu` check.
    $this->setExcludeNotInMenu(FALSE);
    if (!$langcode) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    // This will be like - `category_tree_0_en`.
    $cid = 'category_tree_' . $parent_id . '_' . $langcode;

    // If exists in cache.
    if ($cache = $this->cache->get($cid)) {
      $term_data = $cache->data;
    }
    else {
      // Child terms of given parent term id.
      $term_data = $this->getCategoryTree($langcode, $parent_id);
      $this->cache->set($cid, $term_data, Cache::PERMANENT, ['taxonomy_term:' . self::VOCABULARY_ID]);
    }

    // Reset `$excludeNotInMenu` to default value.
    $this->setExcludeNotInMenu(TRUE);

    return $term_data;
  }

  /**
   * Sets whether include in menu to consider or not.
   *
   * @param bool $exclude_not_in_menu
   *   Include in menu.
   *
   * @return $this
   *   Current object.
   */
  public function setExcludeNotInMenu(bool $exclude_not_in_menu) {
    $this->excludeNotInMenu = $exclude_not_in_menu;
    return $this;
  }

  /**
   * Get the include in menu to skip or not.
   *
   * @return mixed
   *   exclude in menu or not.
   */
  public function getExcludeNotInMenu() {
    return $this->excludeNotInMenu;
  }

  /**
   * Check if category is a sub-category of given term.
   *
   * @param int $sub_category_id
   *   Product category id.
   * @param int $parent_category_id
   *   Selected category id.
   *
   * @return bool
   *   TRUE if $sub_category is a $sub-catgegory of $parent_category.
   */
  public function checkIfSubCategory($sub_category_id, $parent_category_id) {
    if ($sub_category_id === $parent_category_id) {
      return TRUE;
    }
    $ancestors = $this->termStorage->loadAllParents($sub_category_id);
    foreach ($ancestors as $term) {
      if ($term->id() === $parent_category_id) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Get depth level for L1 category.
   *
   * @return int
   *   Depth level.
   */
  public function getL1DepthLevel() {
    return 1;
  }

  /**
   * Check if category is L1.
   *
   * @param \Drupal\taxonomy\TermInterface $category
   *   Category to check.
   *
   * @return bool
   *   TRUE if category is L1.
   */
  public function isCategoryL1(TermInterface $category) {
    $depth = (int) $category->get('depth_level')->getString();
    return $depth === $this->getL1DepthLevel();
  }

  /**
   * Get L1 Parent Category for given category.
   *
   * @param \Drupal\taxonomy\TermInterface $category
   *   Category.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   L1 category for the category.
   */
  public function getL1Category(TermInterface $category) {
    $parents = $this->termStorage->loadAllParents($category->id());
    if (count($parents) < $this->getL1DepthLevel()) {
      return $category;
    }

    $parent = array_reverse($parents, FALSE)[$this->getL1DepthLevel() - 1];
    return $this->entityRepository->getTranslationFromContext($parent);
  }

  /**
   * Get L2 Parent Category for given category.
   *
   * @param \Drupal\taxonomy\TermInterface $category
   *   Category.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   L2 category for the category.
   */
  public function getL2Category(TermInterface $category) {
    $parents = $this->termStorage->loadAllParents($category->id());
    if (count($parents) < $this->getL1DepthLevel()) {
      return $category;
    }

    $parent = array_reverse($parents, FALSE)[1];
    return $this->entityRepository->getTranslationFromContext($parent);
  }

  /**
   * Refresh caches used for mega menu.
   */
  public function refreshCategoryTreeCache() {
    $parents = [];

    // If the L1 depth level is greater than one, invalidate cache for all the
    // parents for that level. This is mainly used for super category feature.
    if ($this->getL1DepthLevel() > 1) {
      $tree = $this->termStorage->loadTree(self::VOCABULARY_ID, 0, $this->getL1DepthLevel() - 1);
      foreach ($tree ?? [] as $term) {
        $parents[] = $term->tid;
      }
    }
    else {
      $parents = [0];
    }

    foreach ($parents as $parent) {
      foreach ($this->languageManager->getLanguages() as $language) {
        $this->getCategoryTreeCached($parent, $language->getId(), TRUE);
      }

      $this->getLogger('ProductCategoryTree')->notice('Cache refreshed for product category tree for id @id.', [
        '@id' => $parent,
      ]);
    }
  }

  /**
   * Get sub categories label and id in current language.
   *
   * Use cache to share the data across multiple sub categories
   * sharing same parent.
   *
   * @param string $langcode
   *   Language code.
   * @param int|string $parent_id
   *   Parent id.
   *
   * @return array
   *   Array of sub category labels in requested language.
   */
  public function getSubCategories(string $langcode, $parent_id = 0) {
    $cid = 'sub_categories:' . $langcode . ':' . $parent_id;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    $sub_categories = [];
    $cache_tags = [
      'taxonomy_term:' . $parent_id,
      'taxonomy_term:' . self::VOCABULARY_ID . ':new',
    ];

    $tree = $this->termStorage->loadTree(self::VOCABULARY_ID, $parent_id, 1, TRUE);
    foreach ($tree ?? [] as $term) {
      // Use only the terms which are enabled.
      if (empty($term->get('field_commerce_status')->getString())) {
        continue;
      }

      $term = $this->entityRepository->getTranslationFromContext($term, $langcode);
      $sub_categories[$term->id()] = $term->label();
      $cache_tags = Cache::mergeTags($cache_tags, $term->getCacheTags());
    }

    $this->cache->set($cid, $sub_categories, Cache::PERMANENT, $cache_tags);
    return $sub_categories;
  }

}
