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
class ProductCategoryTree {

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
   * Highlight image paragraph ids for all terms.
   *
   * @var array
   */
  protected $highlightImages = [];

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
  }

  /**
   * Get top level category items.
   *
   * @param string $langcode
   *   (optional) The language code.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryRootTerms($langcode = NULL) {
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
    }

    $cid = self::CACHE_ID . '_' . $langcode;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    // Get all child terms for the given parent.
    $term_data = $this->getCategoryTree($langcode, 0, FALSE, FALSE);

    $cache_tags = [
      self::CACHE_TAG,
      self::VOCABULARY_ID,
    ];

    $this->cache->set($cid, $term_data, Cache::PERMANENT, $cache_tags);
    return $term_data;
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
      'node_type:department_page',
    ];

    $this->cache->set($cid, $term_data, Cache::PERMANENT, $cache_tags);

    return $term_data;
  }

  /**
   * Get the term tree for 'product_category' vocabulary.
   *
   * Optionally with highlight images and child.
   *
   * @param string $langcode
   *   Language code in which we need term to be displayed.
   * @param int $parent_tid
   *   Parent term id.
   * @param bool $highlight_image
   *   True if include highlight image else false.
   * @param bool $child
   *   True if include child else false.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryTree($langcode, $parent_tid = 0, $highlight_image = TRUE, $child = TRUE) {
    $data = [];

    // Get all child terms for the given parent.
    $terms = $this->allChildTerms($langcode, self::VOCABULARY_ID, $parent_tid);

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

      if ($highlight_image) {
        $data[$term->tid]['highlight_image'] = $this->getHighlightImage($term->tid, $langcode, self::VOCABULARY_ID);
      }

      if ($child) {
        $data[$term->tid]['child'] = $this->getCategoryTree($langcode, $term->tid);
      }

    }

    return $data;
  }

  /**
   * Get highlight image for a term.
   *
   * @param int $tid
   *   Term id.
   * @param string $langcode
   *   Language code.
   * @param string $vid
   *   Vocabulary id.
   *
   * @return array
   *   Highlight image array.
   */
  protected function getHighlightImage($tid, $langcode, $vid) {
    $highlight_images = [];

    // We fetch this from first request and shouldn't be empty. If empty,
    // assuming its first request and prepare data.
    if (empty($this->highlightImages)) {
      $this->getHighLightImages($vid);
    }

    // If no data in paragraph referenced field.
    if (empty($this->highlightImages[$tid])) {
      return $highlight_images;
    }

    foreach ($this->highlightImages[$tid] as $paragraph_id) {
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

      if ($paragraph && !empty($paragraph->get('field_highlight_image'))) {
        $image = $paragraph->get('field_highlight_image')->getValue();
        $image_link = $paragraph->get('field_highlight_link')->getValue();
        $renderable_image = $paragraph->get('field_highlight_image')->view('default');
        if (!empty($image)) {
          $url = Url::fromUri($image_link[0]['uri']);
          $highlight_images[] = [
            'image_link' => $url->toString(),
            'img' => $renderable_image,
          ];
        }
      }
    }

    return $highlight_images;
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
      if ($node->bundle() == 'department_page') {
        $terms = $node->get('field_product_category')->getValue();
        $term = $this->termStorage->load($terms[0]['target_id']);
      }
    }

    // If term is of 'acq_product_category' vocabulary.
    if ($term instanceof TermInterface && $term->getVocabularyId() == self::VOCABULARY_ID) {
      return $term;
    }

    return NULL;
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
    if ($term instanceof TermInterface && $term->getVocabularyId() == self::VOCABULARY_ID) {
      // Get all parents of the given term.
      $parents = $this->termStorage->loadAllParents($term->id());
    }
    return $parents;
  }

  /**
   * Get root parent of given term.
   *
   * OR get parent of the term by getting term from current route.
   *
   * @param null|object $term
   *   (optional) The term object or nothing.
   *
   * @return \Drupal\taxonomy\TermInterface|mixed|null
   *   Return the parent term object or NULL.
   */
  public function getCategoryTermRootParent($term = NULL) {
    if (empty($term) || !$term instanceof  TermInterface) {
      $term = $this->getCategoryTermFromRoute();
    }
    if ($term instanceof TermInterface && $parents = $this->getCategoryTermParents($term)) {
      // Get the top level parent id if parent exists.
      return end($parents);
    }

    return NULL;
  }

  /**
   * Get all child terms of a given term in a language.
   *
   * @param string $langcode
   *   Language code.
   * @param string $vid
   *   Vocabulary id.
   * @param int $parent_tid
   *   Parent term id.
   *
   * @return array
   *   Child term array.
   */
  protected function allChildTerms($langcode, $vid, $parent_tid) {
    $query = $this->connection->select('taxonomy_term_field_data', 'tfd');
    $query->fields('tfd', ['tid', 'name', 'description__value']);
    $query->innerJoin('taxonomy_term_hierarchy', 'tth', 'tth.tid = tfd.tid');
    $query->innerJoin('taxonomy_term__field_category_include_menu', 'ttim', 'ttim.entity_id = tfd.tid AND ttim.langcode = tfd.langcode');
    $query->condition('ttim.field_category_include_menu_value', 1);
    $query->condition('tth.parent', $parent_tid);
    $query->condition('tfd.langcode', $langcode);
    $query->condition('tfd.vid', $vid);
    $query->orderBy('tfd.weight', 'ASC');
    return $query->execute()->fetchAll();
  }

  /**
   * Get highlight image paragraphs id for all terms.
   *
   * @param string $vid
   *   Vocabulary id.
   */
  protected function getHighLightImages($vid) {
    $query = $this->connection->select('taxonomy_term__field_main_menu_highlight', 'tmmh');
    $query->fields('tmmh', ['entity_id', 'field_main_menu_highlight_target_id']);
    $query->condition('tmmh.bundle', $vid);
    $highlight_images = $query->execute()->fetchAll();
    if (!empty($highlight_images)) {
      foreach ($highlight_images as $highlight_image) {
        $this->highlightImages[$highlight_image->entity_id][] = $highlight_image->field_main_menu_highlight_target_id;
      }
    }
  }

}
