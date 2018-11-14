<?php

namespace Drupal\alshaya_paragraphs\Helper;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class MigrateSymmetricToAsymmetric.
 *
 * @package Drupal\alshaya_paragraphs\Helper
 */
class MigrateSymmetricToAsymmetric {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Field Manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * Paragraph Storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paragraphStorage;

  protected $fields = [
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
    'node' => [
      'field_promo_blocks',
      'field_banner',
      'field_delivery_banner',
      'field_promo_banner_full_width',
      'field_related_info',
      'field_slider',
    ],
    'taxonomy_term' => [
      'field_main_menu_highlight',
    ],
    'block_content' => [
      'field_paragraph_content',
    ],
  ];

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * MigrateSymmetricToAsymmetric constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager
   *   Field Manager.
   * @param \Drupal\Core\Database\Connection $db
   *   Database connection.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   Logger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              EntityFieldManagerInterface $field_manager,
                              Connection $db,
                              LoggerChannelInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldManager = $field_manager;
    $this->db = $db;
    $this->logger = $logger;

    $this->paragraphStorage = $this->entityTypeManager->getStorage('paragraph');
  }

  /**
   * Migrate paragraphs for particular entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Node/Term/Block entity to migrate.
   */
  public function migrateEntity(EntityInterface $entity) {
    /** @var \Drupal\node\NodeInterface $entity */
    $fields = $this->fields[$entity->getEntityTypeId()];

    // Hard coded languages here, if we ever get three languages anything
    // below won't work.
    foreach (['en', 'ar'] as $langcode) {
      if (!($entity->hasTranslation($langcode))) {
        continue;
      }

      $entities[$langcode] = $entity->getTranslation($langcode);

      if ($entities[$langcode]->isDefaultTranslation()) {
        $defaultLangcode = $langcode;
      }
    }

    // There is only one translation, no need to go further.
    if (count($entities) < 2) {
      return;
    }

    $default = $entities[$defaultLangcode];
    $translationLangcode = $defaultLangcode === 'en' ? 'ar' : 'en';
    $translation = $entities[$translationLangcode];

    foreach ($fields as $field) {
      if ($default->hasField($field)) {
        $translatedValues = $translation->get($field)->getValue();
        foreach ($default->get($field)->getValue() as $index => $value) {
          $this->paragraphStorage->resetCache();
          $paragraph = $this->paragraphStorage->loadRevision($value['target_revision_id']);
          $translatedParagraph = $this->paragraphStorage->loadRevision($translatedValues[$index]['target_revision_id']);

          if (!($translatedParagraph instanceof Paragraph)) {
            continue;
          }

          if ($translatedParagraph->hasTranslation($translationLangcode)) {
            $translatedParagraph = $translatedParagraph->getTranslation($translationLangcode);
          }

          $this->migrateParagraph($paragraph, $translatedParagraph, $translationLangcode);

          $this->paragraphStorage->resetCache();
          $translatedParagraph = $this->paragraphStorage->loadRevision($translatedValues[$index]['target_revision_id']);

          if ($translatedParagraph->hasTranslation($translationLangcode)) {
            $translatedParagraph = $translatedParagraph->getTranslation($translationLangcode);
          }

          if ($paragraph->hasTranslation($translationLangcode)) {
            $paragraph->removeTranslation($translationLangcode);
          }

          $newTranslatedValues = $this->getTranslatedValues($translatedParagraph);
          $newTranslatedParagraph = $paragraph->addTranslation($translationLangcode, $newTranslatedValues);
          $newTranslatedParagraph->save();
        }
      }
    }
  }

  /**
   * Helper recursive function to migrate child paragraphs.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $original
   *   Original paragraph.
   * @param \Drupal\paragraphs\Entity\Paragraph $translation
   *   Translated paragraph in old way.
   * @param string $translationLangcode
   *   Target translation language code.
   */
  private function migrateParagraph(Paragraph $original, Paragraph $translation, string $translationLangcode) {
    /** @var \Drupal\node\NodeInterface $entity */
    $fields = $this->fields[$original->getEntityTypeId()];

    foreach ($fields as $field) {
      if ($original->hasField($field)) {
        $translatedValues = $translation->get($field)->getValue();
        foreach ($original->get($field)->getValue() as $index => $value) {
          $this->paragraphStorage->resetCache();
          $paragraph = $this->paragraphStorage->loadRevision($value['target_revision_id']);
          $translatedParagraph = $this->paragraphStorage->loadRevision($translatedValues[$index]['target_revision_id']);

          if ($translatedParagraph->hasTranslation($translationLangcode)) {
            $translatedParagraph = $translatedParagraph->getTranslation($translationLangcode);
          }

          $this->migrateParagraph($paragraph, $translatedParagraph, $translationLangcode);

          $this->paragraphStorage->resetCache();
          $translatedParagraph = $this->paragraphStorage->loadRevision($translatedValues[$index]['target_revision_id']);

          if ($translatedParagraph->hasTranslation($translationLangcode)) {
            $translatedParagraph = $translatedParagraph->getTranslation($translationLangcode);
          }

          if ($paragraph->hasTranslation($translationLangcode)) {
            $paragraph->removeTranslation($translationLangcode);
          }

          $newTranslatedValues = $this->getTranslatedValues($translatedParagraph);
          $newTranslatedParagraph = $paragraph->addTranslation($translationLangcode, $newTranslatedValues);
          $newTranslatedParagraph->save();
        }
      }
    }
  }

  /**
   * Migrate paragraphs content to follow new method of translation.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $field_name
   *   Field name.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function migrateContent(string $entity_type, string $field_name) {
    $mainTable = $entity_type . '__' . $field_name;

    $this->logger->info('Migrating content for main table of field @field of type @type', [
      '@field' => $field_name,
      '@type' => $entity_type,
    ]);

    $data = $this->getData($mainTable);

    foreach ($data as $row) {
      $this->processRow($row, $mainTable, $field_name);
    }
  }

  /**
   * Get data for particular field from DB.
   *
   * @param string $table
   *   Get data from table.
   *
   * @return array
   *   Grouped data array.
   */
  private function getData(string $table): array {
    try {
      $query = $this->db->select($table);
      $query->fields($table);
      $result = $query->execute()->fetchAll();

      $rows = [];

      foreach ($result as $row) {
        $row = (array) $row;

        $key = implode(':', [
          $row['entity_id'],
          $row['revision_id'],
          $row['delta'],
        ]);

        $rows[$key][$row['langcode']] = $row;
      }

      return $rows;
    }
    catch (\Exception $e) {
      $this->logger->warning('Error occurred while getting data from @table. Message: @message', [
        '@table' => $table,
        '@message' => $e->getMessage(),
      ]);
    }

    return [];
  }

  /**
   * Process a particular paragraph field content.
   *
   * @param array $row
   *   Array containing content of particular paragraph field in all languages.
   * @param string $table
   *   Table name.
   * @param string $field
   *   Field name.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function processRow(array $row, string $table, string $field) {
    $this->paragraphStorage->resetCache();

    // Do not process if there is content for only one language.
    if (count($row) === 1) {
      $this->logger->warning('Not processing row with single translation. Row: @row', [
        '@row' => json_encode($row),
      ]);
      return;
    }

    // We know we will have only two languages so hard-coded here.
    $en = $row['en'];
    $ar = $row['ar'];

    $revision_field = $field . '_target_revision_id';
    $target_field = $field . '_target_id';

    /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
    $paragraph = $this->paragraphStorage->loadRevision($en[$revision_field]);

    if (!($paragraph instanceof Paragraph)) {
      $this->logger->warning('Not processing as not able to load main paragraph itself. Row: @row', [
        '@row' => json_encode($row),
      ]);

      return;
    }

    if ($en[$revision_field] === $ar[$revision_field] && $en[$target_field] === $ar[$target_field]) {
      $this->logger->info('Not processing as translations already same. Row: @row', [
        '@row' => json_encode($row),
      ]);

      return;
    }

    if ($paragraph->isDefaultTranslation()) {
      $default = $en;
      $translation = $ar;
    }
    else {
      $paragraph = $this->paragraphStorage->loadRevision($ar[$revision_field]);

      if (!($paragraph instanceof Paragraph)) {
        $this->logger->warning('Not processing as not able to load main paragraph itself. Row: @row', [
          '@row' => json_encode($row),
        ]);

        return;
      }

      $default = $ar;
      $translation = $en;
    }

    /** @var \Drupal\paragraphs\Entity\Paragraph $translatedParagraph */
    $translatedParagraph = $this->paragraphStorage->loadRevision($translation[$revision_field]);

    if (!($translatedParagraph instanceof Paragraph)) {
      $this->logger->warning('Not processing as not able to load data for translation. Row: @row', [
        '@row' => json_encode($row),
      ]);

      return;
    }

    if ($translatedParagraph->hasTranslation($translation['langcode'])) {
      $translatedParagraph = $translatedParagraph->getTranslation($translation['langcode']);
    }

    $translatedValues = $this->getTranslatedValues($translatedParagraph);

    // Remove translation if already available.
    if ($paragraph->hasTranslation($translation['langcode'])) {
      $paragraph->removeTranslation($translation['langcode']);
    }

    $newTranslatedParagraph = $paragraph->addTranslation($translation['langcode'], $translatedValues);
    try {
      $newTranslatedParagraph->save();
    }
    catch (\Exception $e) {
      $this->logger->warning('Error occurred while saving new translation for row: @row. Message: @message', [
        '@row' => json_encode($row),
        '@message' => $e->getMessage(),
      ]);

      return;
    }

    $query = $this->db->update($table);
    $query->condition('entity_id', $translation['entity_id']);
    $query->condition('revision_id', $translation['revision_id']);
    $query->condition('delta', $translation['delta']);

    $fields = [
      $revision_field => $default[$revision_field],
      $target_field => $default[$target_field],
    ];

    $query->fields($fields);
    $query->execute();

    // Delete the old translated paragraph.
    $translatedParagraph->delete();

    $this->logger->info('Migrated data for row: @row.', [
      '@row' => json_encode($row),
    ]);
  }

  /**
   * Get translated values from paragraph entity.
   *
   * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
   *   Entity to get translated values from.
   *
   * @return array
   *   Cleaned translated values array.
   */
  private function getTranslatedValues(Paragraph $paragraph): array {
    $translatedValues = $paragraph->toArray();

    // Remove unwanted values.
    $fields_to_remove = [
      'id',
      'uuid',
      'revision_id',
      'langcode',
      'type',
      'default_langcode',
      'revision_translation_affected',
      'content_translation_source',
      'content_translation_outdated',
      'content_translation_changed',
      'behavior_settings',
      'parent_id',
      'parent_type',
      'parent_field_name',
      'status',
    ];

    foreach ($fields_to_remove as $field_to_remove) {
      unset($translatedValues[$field_to_remove]);
    }

    return $translatedValues;
  }

}
