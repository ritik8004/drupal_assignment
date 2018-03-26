<?php

namespace Drupal\alshaya_main_menu;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache Backend service for alshaya.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache,
                              Connection $connection) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->languageManager = $language_manager;
    $this->cache = $cache;
    $this->connection = $connection;
  }

  /**
   * Get the term tree for 'product_category' vocabulary from cache or fresh.
   *
   * @return array
   *   Processed term data from cache if available or fresh.
   */
  public function getCategoryTreeCached() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $cid = self::CACHE_ID . '_' . $langcode;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    $term_data = $this->getCategoryTree($langcode);

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
   * @param string $langcode
   *   Language code in which we need term to be displayed.
   * @param int $parent_tid
   *   Parent term id.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryTree($langcode, $parent_tid = 0) {
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

      $data[$term->tid]['highlight_image'] = $this->getHighlightImage($term->tid, $langcode, self::VOCABULARY_ID);
      $data[$term->tid]['child'] = $this->getCategoryTree($langcode, $term->tid);
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

    $highlight_field = $this->getHighLightImages($langcode, $vid, $tid);

    // If no data in paragraph referenced field.
    if (empty($highlight_field)) {
      return $highlight_images;
    }

    foreach ($highlight_field as $paragraph_id) {
      $paragraph_id = $paragraph_id->field_main_menu_highlight_target_id;
      // Load paragraph entity.
      $paragraph = Paragraph::load($paragraph_id);

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
    $query->innerJoin('taxonomy_term_hierarchy', 'tth', 'tth.tid=tfd.tid');
    $query->innerJoin('taxonomy_term__field_category_include_menu', 'ttim', 'ttim.entity_id=tfd.tid');
    $query->condition('ttim.field_category_include_menu_value', 1);
    $query->condition('tth.parent', $parent_tid);
    $query->condition('ttim.langcode', $langcode);
    $query->condition('tfd.langcode', $langcode);
    $query->condition('tfd.vid', $vid);
    $query->orderBy('tfd.weight', 'ASC');
    return $query->execute()->fetchAll();
  }

  /**
   * Get highlight image paragraphs id for a given term.
   *
   * @param string $langcode
   *   Language code.
   * @param string $vid
   *   Vocabulary id.
   * @param int $tid
   *   Term id.
   *
   * @return array
   *   Highlight image ids.
   */
  protected function getHighLightImages($langcode, $vid, $tid) {
    $query = $this->connection->select('taxonomy_term__field_main_menu_highlight', 'tmmh');
    $query->fields('tmmh', ['field_main_menu_highlight_target_id']);
    $query->condition('tmmh.langcode', $langcode);
    $query->condition('tmmh.entity_id', $tid);
    $query->condition('tmmh.bundle', $vid);
    return $query->execute()->fetchAll();
  }

}
