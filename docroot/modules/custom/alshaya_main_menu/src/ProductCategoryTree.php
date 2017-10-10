<?php

namespace Drupal\alshaya_main_menu;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
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
   * Entity repository.
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
   * ProductCategoryTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, LanguageManagerInterface $language_manager) {
    $this->termStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
  }

  /**
   * Get the term tree for 'product_category' vocabulary from cache or fresh.
   *
   * @return array
   *   Processed term data from cache if available or fresh.
   */
  public function getCategoryTreeCached() {
    $cid = self::CACHE_ID . '_' . $this->languageManager->getCurrentLanguage()->getId();

    if ($term_data = \Drupal::cache(self::CACHE_BIN)->get($cid)) {
      return $term_data->data;
    }

    $term_data = $this->getCategoryTree();

    $cache_tags = [
      self::CACHE_TAG,
      'node_type:department_page',
    ];

    \Drupal::cache(self::CACHE_BIN)->set($cid, $term_data, Cache::PERMANENT, $cache_tags);

    return $term_data;
  }

  /**
   * Get the term tree for 'product_category' vocabulary.
   *
   * @param int $parent_tid
   *   Parent term id.
   *
   * @return array
   *   Processed term data.
   */
  protected function getCategoryTree($parent_tid = 0) {
    $data = [];

    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->termStorage->loadTree(self::VOCABULARY_ID, $parent_tid, 1, TRUE);

    if (empty($terms)) {
      return [];
    }

    // Get all the department pages.
    $alshaya_department_pages = alshaya_department_page_get_pages();

    foreach ($terms as $term) {
      // For language specific data.
      $term = $this->entityRepository->getTranslationFromContext($term);

      // Get value of boolean field which will decide if we show/hide this
      // term and child terms in the menu.
      $include_in_menu = $term->get('field_category_include_menu')->getValue();

      // Hide the menu if there is a value in the field and it is FALSE.
      if (!empty($include_in_menu) && !($include_in_menu[0]['value'])) {
        continue;
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

      $data[$term->id()]['highlight_image'] = $this->getHighlightImage($term);

      // Check if there is a department page available for this term.
      if (isset($alshaya_department_pages[$term->id()])) {
        $nid = $alshaya_department_pages[$term->id()];

        // Use the path of node instead of term path.
        $data[$term->id()]['path'] = Url::fromRoute('entity.node.canonical', ['node' => $nid])->toString();
      }

      $data[$term->id()]['child'] = $this->getCategoryTree($term->id());
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

}
