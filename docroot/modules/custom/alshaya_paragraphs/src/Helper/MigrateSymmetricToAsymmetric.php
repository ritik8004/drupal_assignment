<?php

namespace Drupal\alshaya_paragraphs\Helper;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
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

    $this->logger->info('Migrating content for revision table of field @field of type @type', [
      '@field' => $field_name,
      '@type' => $entity_type,
    ]);

    $revisionTable = $entity_type . '_revision__' . $field_name;
    $data = $this->getData($revisionTable);
    foreach ($data as $row) {
      $this->processRow($row, $revisionTable, $field_name);
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
      $this->logger->warning('Not processing as translations already same. Row: @row', [
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

    $translatedValues = $translatedParagraph->toArray();

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

    if ($paragraph->hasTranslation($translation['langcode'])) {
      $paragraph->removeTranslation($translation['langcode']);
    }

    $newTranslatedParagraph = $paragraph->addTranslation($translation['langcode'], $translatedValues);
    $newTranslatedParagraph->save();

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

}
