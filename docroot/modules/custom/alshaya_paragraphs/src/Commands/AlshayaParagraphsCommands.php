<?php

namespace Drupal\alshaya_paragraphs\Commands;

use Drupal\alshaya_paragraphs\Helper\MigrateSymmetricToAsymmetric;
use Drush\Commands\DrushCommands;

/**
 * AlshayaParagraphsCommands class.
 */
class AlshayaParagraphsCommands extends DrushCommands {

  /**
   * Migration Utility.
   *
   * @var \Drupal\alshaya_paragraphs\Helper\MigrateSymmetricToAsymmetric
   */
  private $migrateUtility;

  /**
   * AlshayaParagraphsCommands constructor.
   *
   * @param \Drupal\alshaya_paragraphs\Helper\MigrateSymmetricToAsymmetric $migrate_utility
   *   Migration utility.
   */
  public function __construct(MigrateSymmetricToAsymmetric $migrate_utility) {
    $this->migrateUtility = $migrate_utility;
  }

  /**
   * Code to be executed only once post install.
   *
   * @command alshaya_paragraphs:migrate-paragraph-translations
   *
   * @validate-module-enabled alshaya_paragraphs
   *
   * @aliases migrate-paragraphs
   */
  public function migrateParagraphs() {
    $fields = [
      'node' => [
        'field_banner',
        'field_delivery_banner',
        'field_promo_banner_full_width',
        'field_promo_blocks',
        'field_related_info',
        'field_slider',
      ],
      'taxonomy_term' => [
        'field_main_menu_highlight',
      ],
      'block_content' => [
        'field_paragraph_content',
      ],
      'paragraph' => [
        'field_1_row_2_col_1',
        'field_1_row_2_col_2',
        'field_1_row_2_col',
        'field_1_row_promo_block',
        'field_1st_col_promo_block',
        'field_2nd_col_promo_block',
        'field_mobile_banner_image',
        'field_promo_block_button',
        'field_promo_block',
      ],
    ];

    foreach ($fields as $entity_type => $entity_fields) {
      foreach ($entity_fields as $entity_field) {
        $this->migrateUtility->migrateContent($entity_type, $entity_field);
      }
    }
  }

}
