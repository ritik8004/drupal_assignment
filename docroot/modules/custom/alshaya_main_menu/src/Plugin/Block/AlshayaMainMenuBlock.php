<?php

namespace Drupal\alshaya_main_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Drupal\taxonomy\TermInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alshaya main menu block.
 *
 * @Block(
 *   id = "alshaya_main_menu",
 *   admin_label = @Translation("Alshaya main menu")
 * )
 */
class AlshayaMainMenuBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * AlshayaMegaMenuBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin defination.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $term_data = $this->getChildTerms();

    // If no data, no need to render the block.
    if (empty($term_data)) {
      return [
        '#markup' => '',
      ];
    }

    // Removes the first term 'default category' as its not required.
    $key = key($term_data);
    $term_data = $term_data[$key]['child'];

    return [
      '#theme' => 'alshaya_main_menu',
      '#term_tree' => $term_data,
    ];
  }

  /**
   * Prepares the tree structure of the terms.
   *
   * @param int $parent_tid
   *   The parent tid.
   * @param int $depth
   *   Depth of term to find.
   *
   * @return array
   *   Tree structure of terms.
   */
  protected function getChildTerms($parent_tid = 0, $depth = 1) {
    $data = [];

    /* @var \Drupal\taxonomy\TermInterface[] $terms */
    $terms = $this->entityManager->getStorage('taxonomy_term')->loadTree('acq_product_category', $parent_tid, $depth, TRUE);
    if ($terms) {
      foreach ($terms as $term) {
        $data[$term->id()] = [
          'label' => $term->label(),
          'description' => $term->getDescription(),
          'id' => $term->id(),
          'highlight_image' => $this->getHighlightImage($term),
        ];
        $data[$term->id()]['child'] = $this->getChildTerms($term->id());
      }
    }

    return $data;
  }

  /**
   * Get the highlight image for the given term.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   Term object.
   *
   * @return array
   *   Array of image url.
   */
  protected function getHighlightImage(TermInterface $term) {
    $highlight_images = [];

    if ($highlight_field = $term->get('field_main_menu_highlight')) {

      // If no data in paragraph referenced field.
      if (empty($highlight_field->getValue())) {
        return $highlight_images;
      }

      $paragraph_id = $highlight_field->getValue()[0]['target_id'];

      // Load paragraph entity.
      $paragraph = Paragraph::load($paragraph_id);

      if ($paragraph && !empty($paragraph->get('field_highlight_image'))) {
        $images = $paragraph->get('field_highlight_image')->getValue();
        if (!empty($images)) {
          foreach ($images as $image) {
            $file = File::load($image['target_id']);
            $highlighted_images[] = file_create_url($file->getFileUri());
          }
        }
      }
    }

    return $highlight_images;
  }

}
