<?php

namespace Drupal\alshaya_main_menu;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
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
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    CacheBackendInterface $cache,
    RouteMatchInterface $route_match) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->languageManager = $language_manager;
    $this->cache = $cache;
    $this->routeMatch = $route_match;
  }

  /**
   * Get top level category items.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryRootTerms() {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $cid = self::CACHE_ID . '_' . $langcode;

    if ($term_data = $this->cache->get($cid)) {
      return $term_data->data;
    }

    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->termStorage->loadTree(self::VOCABULARY_ID, 0, 1, TRUE);
    $term_data = $this->getCategoryTermData($langcode, $terms, FALSE, FALSE);

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
      self::VOCABULARY_ID . '_' . $langcode . '_' . $parent_id,
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
    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->termStorage->loadTree(self::VOCABULARY_ID, $parent_tid, 1, TRUE);

    if (empty($terms)) {
      return [];
    }
    return $this->getCategoryTermData($langcode, $terms);
  }

  /**
   * Create terms array of menu items with highlight images and child.
   *
   * @param string $langcode
   *   Language code in which we need term to be displayed.
   * @param array $terms
   *   The array of terms to load.
   * @param bool $highlight_image
   *   True if include highlight image else false.
   * @param bool $child
   *   True if include child else false.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryTermData($langcode, array $terms, $highlight_image = TRUE, $child = TRUE) {
    $data = [];
    foreach ($terms as $term) {
      // We don't show the term in menu if translation not available.
      if (!$term->hasTranslation($langcode)) {
        continue;
      }

      // Load translation for requested langcode.
      $term = $term->getTranslation($langcode);

      if ($term->hasField('field_category_include_menu')) {
        // Get value of boolean field which will decide if we show/hide this
        // term and child terms in the menu.
        $include_in_menu = $term->get('field_category_include_menu')->getValue();

        // Hide the menu if there is a value in the field and it is FALSE.
        if (!empty($include_in_menu) && !($include_in_menu[0]['value'])) {
          continue;
        }
      }

      $data[$term->id()] = [
        'label' => $term->label(),
        'description' => [
          '#markup' => $term->getDescription(),
        ],
        'id' => $term->id(),
        'path' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()])->toString(),
        'active_class' => '',
      ];

      if ($highlight_image) {
        $data[$term->id()]['highlight_image'] = $this->getHighlightImage($term);
      }

      if ($child) {
        $data[$term->id()]['child'] = $this->getCategoryTree($langcode, $term->id());
      }
    }
    return $data;
  }

  /**
   * Get highlight image for a 'product_category' term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term object.
   *
   * @return array
   *   Highlight image array.
   */
  protected function getHighlightImage(TermInterface $term) {
    // Get the current language code.
    $language = $this->languageManager->getCurrentLanguage()->getId();

    $highlight_images = [];

    if ($highlight_field = $term->get('field_main_menu_highlight')) {

      // If no data in paragraph referenced field.
      if (empty($highlight_field->getValue())) {
        return $highlight_images;
      }

      foreach ($highlight_field->getValue() as $paragraph_id) {
        $paragraph_id = $paragraph_id['target_id'];

        // Load paragraph entity.
        $paragraph = Paragraph::load($paragraph_id);

        // Get the translation of the paragraph if exists.
        if ($paragraph->hasTranslation($language)) {
          // Replace the current paragraph with translated one.
          $paragraph = $paragraph->getTranslation($language);
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

}
