<?php

namespace Drupal\alshaya_paragraphs_rename;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Class AlshayaParagraphsRename.
 */
class AlshayaParagraphsRename {

  /**
   * Entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity field manager object.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Creates a new storage instance for paragraph.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paragraph;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translation;

  /**
   * AlshayaParagraphsRename constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager object.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The string translation service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    Connection $connection,
    TranslationInterface $translation
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->paragraph = $entity_type_manager->getStorage('paragraph');
    $this->entityFieldManager = $entity_field_manager;
    $this->connection = $connection;
    $this->translation = $translation;
  }

  /**
   * Get the base and data table names of given entity type.
   */
  protected function getParagraphTables() {
    // Get the names of the base tables.
    $base_table_names = [];
    $base_table_names[] = $this->paragraph->getBaseTable();
    $base_table_names[] = $this->paragraph->getDataTable();
    return $base_table_names;
  }

  /**
   * Get the field and field revision table names of given bundle.
   *
   * @param string $bundle
   *   The bundle name.
   *
   * @return array
   *   Return array of field and field revision table names.
   */
  protected function getBundleFields($bundle) {
    $data = &drupal_static(__FUNCTION__);
    if (isset($data)) {
      return $data;
    }
    $source_bundle_fields = $this->entityFieldManager->getFieldDefinitions('paragraph', $bundle);

    $table_mapping = $this->paragraph->getTableMapping();
    // Get the names of the field tables for fields on the service node type.
    $data = ['field_names' => [], 'tables' => []];
    foreach ($source_bundle_fields as $field) {
      if ($field instanceof FieldConfig) {
        $data['field_names'][] = $field->getName();
        $field_table = $table_mapping->getFieldTableName($field->getName());
        $data['tables'][] = $field_table;

        $field_storage_definition = $field->getFieldStorageDefinition();
        $field_revision_table = $table_mapping->getDedicatedRevisionTableName($field_storage_definition);

        // Field revision tables DO have the bundle!
        $data['tables'][] = $field_revision_table;
      }
    }

    return $data;
  }

  /**
   * Get the paragraph item ids of given bundle.
   *
   * @param string $bundle
   *   The pagraph bundle name.
   *
   * @return array|int
   *   Return array of paragraph item ids.
   */
  protected function getPargraphItems($bundle) {
    return $this->paragraph->getQuery()
      ->condition('type', $bundle)
      ->execute();
  }

  /**
   * Rename paragraph bundle to new name.
   *
   * @param string $current_name
   *   Existing bundle name.
   * @param string $new_name
   *   New bundle name.
   */
  public function renameParagraphBundle($current_name, $new_name) {
    $migrate_ids = $this->getPargraphItems($current_name);
    if (count($migrate_ids) == 0) {
      return;
    }

    try {
      $base_table_names = $this->getParagraphTables();
      foreach ($base_table_names as $table_name) {
        $this->connection
          ->update($table_name)
          ->fields(['type' => $new_name])
          ->condition('id', $migrate_ids, 'IN')
          ->execute();
      }

      $field_table_names = $this->getBundleFields($current_name)['tables'];
      foreach ($field_table_names as $table_name) {
        $this->connection
          ->update($table_name)
          ->fields(['bundle' => $new_name])
          ->condition('entity_id', $migrate_ids, 'IN')
          ->condition('bundle', $current_name)
          ->execute();
      }
    }
    catch (DatabaseExceptionWrapper $e) {
      throw $e;
    }
  }

  /**
   * Delete given paragraph bundle.
   *
   * @param string $bundle
   *   The bundle to delete.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteParagraphBundle($bundle) {
    $num_paragraphs = $this->paragraph->getQuery()
      ->condition('type', $bundle)
      ->count()
      ->execute();

    if ($num_paragraphs) {
      return $this->translation->formatPlural($num_paragraphs,
          '%type Paragraphs type is used by 1 piece of content on your site. You can not remove this %type Paragraphs type until you have removed all from the content.',
          '%type Paragraphs type is used by @count pieces of content on your site. You may not remove %type Paragraphs type until you have removed all from the content.', ['%type' => $this->entity->label()]);
    }
    else {
      $fields = $this->getBundleFields($bundle)['field_names'];
      foreach ($fields as $field_name) {
        $field_config = FieldConfig::loadByName('paragraph', $bundle, $field_name);
        if ($field_config instanceof FieldConfig) {
          $field_config->delete();
        }
      }

      $paragraph_type = $this->entityTypeManager->getStorage('paragraphs_type')->load($bundle);
      if ($paragraph_type instanceof ParagraphsType) {
        $paragraph_type->delete();
      }

      // @codingStandardsIgnoreLine
      return t('Paragraph type "@type" successfully deleted.', ['@type' => $bundle]);
    }
  }

}
