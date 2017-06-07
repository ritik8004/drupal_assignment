<?php

namespace Drupal\alshaya_main_menu;

use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Class ProductCategoryTree.
 */
class ProductCategoryTree {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
  }

  /**
   * Get the term tree for 'product_category' vocabulary.
   *
   * @param int $parent_tid
   *   Parent term id.
   * @param int $depth
   *   Term depth.
   * @param bool $highlight_image
   *   If need to get highlight image or not.
   *
   * @return array
   *   Processed term data.
   */
  public function getCategoryTree($parent_tid = 0, $depth = 1, $highlight_image = TRUE) {
    $data = [];
    $cache_tags = [];

    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadTree('acq_product_category', $parent_tid, $depth, TRUE);
    if ($terms) {
      foreach ($terms as $term) {
        // For language specific data.
        $term = $this->entityRepository->getTranslationFromContext($term);

        // For cache tag bubbling up.
        $cache_tags[] = 'taxonomy_term:' . $term->id();

        // Get value of boolean field which will decide if we show/hide this
        // term and child terms in the menu.
        $include_in_menu = $term->get('field_category_include_menu')->getValue();

        // Hide the menu if there is a value in the field and it is FALSE.
        if (!empty($include_in_menu) && !($include_in_menu[0]['value'])) {
          continue;
        }

        $data[$term->id()] = [
          'label' => $term->label(),
          'description' => $term->getDescription(),
          'id' => $term->id(),
          'path' => Url::fromRoute('entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()])
            ->toString(),
          'active_class' => '',
        ];

        if ($highlight_image) {
          $data[$term->id()] += ['highlight_image' => $this->getHighlightImage($term)];
        }

        // Check if there is a department page available for this term.
        if ($nid = alshaya_department_page_page_exists($term->id())) {
          // Use the path of node instead of term path.
          $data[$term->id()]['path'] = Url::fromRoute('entity.node.canonical', ['node' => $nid])
            ->toString();
        }

        $data[$term->id()]['child'] = $this->getCategoryTree($term->id());
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
  public function getHighlightImage(TermInterface $term) {
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

        // Get the current language code.
        $language = $this->languageManager->getCurrentLanguage()->getId();

        // Get the translation of the paragraph if exists.
        if ($paragraph->hasTranslation($language)) {
          // Replace the current paragraph with translated one.
          $paragraph = $paragraph->getTranslation($language);
        }

        if ($paragraph && !empty($paragraph->get('field_highlight_image'))) {
          $image = $paragraph->get('field_highlight_image')->getValue();
          $image_link = $paragraph->get('field_highlight_link')->getValue();
          if (!empty($image)) {
            $file = File::load($image[0]['target_id']);
            $url = Url::fromUri($image_link[0]['uri']);
            $highlight_images[] = [
              'image_link' => $url->toString(),
              'img' => file_create_url($file->getFileUri()),
            ];
          }
        }
      }
    }

    return $highlight_images;
  }

}
