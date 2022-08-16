<?php

namespace Drupal\acq_sku;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Psr\Log\LoggerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Database\Connection;

/**
 * Class SKU Fields Manager.
 *
 * @package Drupal\acq_sku
 */
class SKUFieldsManager {

  public const BASE_FIELD_ADDITIONS_CONFIG = 'acq_sku.base_field_additions';

  /**
   * The Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The Entity Type Manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The Entity Definition Update Manager service.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  private $entityDefinitionUpdateManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * SKUFieldsManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config Factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module Handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $entity_definition_update_manager
   *   The Entity Definition Update Manager service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Logger.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ModuleHandlerInterface $module_handler,
                              EntityTypeManagerInterface $entity_type_manager,
                              EntityDefinitionUpdateManagerInterface $entity_definition_update_manager,
                              LoggerInterface $logger,
                              Connection $connection) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDefinitionUpdateManager = $entity_definition_update_manager;
    $this->logger = $logger;
    $this->connection = $connection;
  }

  /**
   * Function to add all new field definitions from custom modules to SKU Base.
   */
  public function addFields() {
    $this->logger->info('addFields() invoked to add newly added base fields to SKU.');
    // Get all the additional fields from all custom modules.
    $fields = $this->getAllCustomFields();

    // Store the fields in config.
    $config = $this->configFactory->getEditable(self::BASE_FIELD_ADDITIONS_CONFIG);
    $existing_fields = $config->getRawData();

    $fields = array_diff_key($fields, $existing_fields);
    $existing_fields = array_merge($existing_fields, $fields);
    $config->setData($existing_fields)->save();

    if ($fields) {
      $this->logger->info('Adding new fields %fields.', [
        '%fields' => json_encode($fields, JSON_THROW_ON_ERROR),
      ]);

      // Adding new fields.
      foreach ($fields as $field) {
        $storage_definition = $this->getFieldDefinitionFromInfo($field);
        $this->entityDefinitionUpdateManager->installFieldStorageDefinition(
          'attr_' . $field['source'],
          'acq_sku',
          'acq_sku',
          $storage_definition
        );
      }
      // Allow other modules to take some action after the fields are added.
      $this->moduleHandler->invokeAll('acq_sku_base_fields_updated', [
        $fields,
        'add',
      ]);
    }
    else {
      $this->logger->warning('No new fields found to add.');
    }

  }

  /**
   * Remove base field from SKU entity.
   *
   * Note: Calling function needs to take care of clearing data.
   *
   * @param string $field_code
   *   Field code to remove.
   */
  public function removeField($field_code) {
    $config = $this->configFactory->getEditable(self::BASE_FIELD_ADDITIONS_CONFIG);
    $fields = $config->getRawData();

    if (!isset($fields[$field_code])) {
      return;
    }

    $field = $fields[$field_code];
    unset($fields[$field_code]);
    $config->setData($fields)->save();

    $this->entityTypeManager->clearCachedDefinitions();

    $fields_removed = [
      $field_code => $field,
    ];

    $this->moduleHandler->invokeAll('acq_sku_base_fields_updated', [
      $fields_removed,
      'remove',
    ]);
  }

  /**
   * Function to update field definitions for the additional SKU base fields.
   *
   * This will not update the actual field but only additional information used
   * in custom code like field is configurable or not, indexable or not.
   *
   * It will not do anything except updating the config. Be very careful when
   * using this.
   *
   * @param string $field_code
   *   Field code.
   * @param array $field
   *   Field definition.
   *
   * @throws \Exception
   *   Throws exception if field doesn't exist in config.
   */
  public function updateFieldMetaInfo($field_code, array $field) {
    $config = $this->configFactory->getEditable(self::BASE_FIELD_ADDITIONS_CONFIG);
    $existing_fields = $config->getRawData();

    if (empty($existing_fields[$field_code])) {
      throw new \Exception('Field not available, try adding it first.');
    }

    // Checks to avoid errors.
    $field_structure_info = [
      'type',
      'cardinality',
    ];

    foreach ($field_structure_info as $info) {
      if (isset($field[$info]) && $field['type'] != $existing_fields[$field_code]['type']) {
        throw new \Exception('Can not modify field structure.');
      }
    }

    // Need to apply entity updates for following.
    $field_labels_info = [
      'label',
      'description',
      'visible_view',
      'visible_form',
      'weight',
    ];

    foreach ($field_labels_info as $info) {
      if (isset($field[$info]) && $field['type'] != $existing_fields[$field_code]['type']) {
        break;
      }
    }

    $existing_fields[$field_code] = array_replace($existing_fields[$field_code], $field);
    $config->setData($existing_fields)->save();
  }

  /**
   * Update field type for existing attribute.
   *
   * @param array $attributes
   *   List of product attributes.
   */
  public function updateFieldType(array $attributes) {
    foreach ($attributes as $source_column) {
      try {
        // Fetch and store existing attribute data in temporary variable.
        $query = $this->connection->select('acq_sku_field_data', 'asfd');
        $query->fields('asfd', ['id', 'type', 'langcode', $source_column]);
        $query->isNotNull('asfd.' . $source_column);
        $result = $query->execute()->fetchAll();

        // First remove the field. This is required or drupal won't allow
        // the change in storage of the field.
        $field = str_replace('attr_', '', $source_column);
        $this->removeField($field);
        // Remove unsed field from table acq_sku_field_data.
        $this->connection->schema()->dropField('acq_sku_field_data', $source_column);
        // Then re-add the field.
        $this->addFields();

        // If data is available in temporary variable,
        // then re-store it in new table.
        if (!empty($result)) {
          $destination_table = 'acq_sku_field_data';
          $destination_column = $source_column . '__value';

          // Updata data for new field.
          foreach ($result as $rs) {
            // Update data in new destination attribute.
            $query = $this->connection->update($destination_table)->fields([
              $destination_column => $rs->{$source_column},
            ])
              ->condition('id', $rs->id, '=')
              ->condition('langcode', $rs->langcode, '=');
            $query->execute();
          }
        }
      }
      catch (\Exception $e) {
        $this->logger->warning('Failed to migrate records for updated fields: @message', [
          '@message' => $e->getMessage(),
        ]);
      }
    }
  }

  /**
   * Get all existing field additions.
   *
   * @param bool $clear_static_cache
   *   Whether static cache needs to clear or not.
   *
   * @return array
   *   Existing field additions.
   */
  public function getFieldAdditions($clear_static_cache = FALSE) {
    if ($clear_static_cache) {
      $this->configFactory->clearStaticCache();
    }
    return $this->configFactory->get(self::BASE_FIELD_ADDITIONS_CONFIG)->getRawData();
  }

  /**
   * Get all fields defined in custom modules.
   *
   * @return array
   *   All fields defined in custom modules.
   */
  private function getAllCustomFields() {
    $fields = [];

    $this->moduleHandler->alter('acq_sku_base_field_additions', $fields);

    foreach ($fields as $field_code => $field) {
      $fields[$field_code] = $this->applyDefaults($field_code, $field);
    }

    return $fields;
  }

  /**
   * Function to apply defaults and complete field definition.
   *
   * @param string $field_code
   *   Field code.
   * @param array $field
   *   Field definition.
   *
   * @return array
   *   Field definition with all defaults applied.
   */
  private function applyDefaults($field_code, array $field) {
    $defaults = $this->getDefaults();

    if (empty($field['source'])) {
      $field['source'] = $field_code;
    }

    // We will always have label, still we do a check to avoid errors.
    if (empty($field['label'])) {
      $field['label'] = $field_code;
    }

    // Add description if empty.
    if (empty($field['description'])) {
      $field['description'] = str_replace('[label]', $field['label'], $defaults['description']);
    }

    // Merge all other defaults.
    $field += $defaults;

    return $field;
  }

  /**
   * Returns an associative array containing all required values.
   *
   * It also has default values set.
   *
   * @returns array
   *   Associative array containing all required values with defaults set.
   */
  private function getDefaults() {
    return [
      // (Required) Label to be used for admin forms and display.
      'label' => '',
      // Soruce field code to use for reading from product data.
      'source' => '',
      // Description of the field to be used in admin forms.
      'description' => '[label] attribute for the product.',
      // (Required) Parent key in the array where to look for data.
      'parent' => 'attributes',
      // Type of the field.
      'type' => 'attribute',
      // Number of values allowed to be stored.
      'cardinality' => 1,
      // (Optional) Should the data be stored as serialized.
      'serialize' => 0,
      // Whether the field is part of configurable options.
      'configurable' => 0,
      // Default weight of the field in form and display.
      'weight' => NULL,
      // Whether the field should be visible while viewing content.
      'visible_view' => 0,
      // Whether the field should be visible in form.
      'visible_form' => 1,
      // Whether the field should be translatable or not.
      'translatable' => 1,
    ];
  }

  /**
   * Reset facets and facet blocks for base fields from ones provided by config.
   *
   * Also reset the base fields data in main config.
   */
  public function resetBaseFields() {
    $fields = $this->getAllCustomFields();
    $config = $this->configFactory->getEditable(self::BASE_FIELD_ADDITIONS_CONFIG);
    $config->setData($fields);
    $config->save();
    $this->moduleHandler->invokeAll('acq_sku_base_fields_updated', [
      $fields,
      'add',
    ]);
  }

  /**
   * Returns field definition based on its type.
   *
   * @param array $field_info
   *   Field Info array.
   * @param int $weight
   *   Default weight of the field.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition|null
   *   Return base field definition.
   */
  public function getFieldDefinitionFromInfo(array $field_info, $weight = 10) {
    $fieldDefinition = NULL;
    switch ($field_info['type']) {
      case 'attribute':
      case 'string':
        $fieldDefinition = BaseFieldDefinition::create('string');

        if ($field_info['visible_view']) {
          $fieldDefinition->setDisplayOptions('view', [
            'label' => 'above',
            'type' => 'string',
            'weight' => $weight,
          ]);
        }

        if ($field_info['visible_form']) {
          $fieldDefinition->setDisplayOptions('form', [
            'type' => 'string_textfield',
            'weight' => $weight,
          ]);
        }
        break;

      case 'text_long':
        $fieldDefinition = BaseFieldDefinition::create('text_long');

        if ($field_info['visible_view']) {
          $fieldDefinition->setDisplayOptions('view', [
            'label' => 'hidden',
            'type' => 'text_default',
            'weight' => $weight,
          ]);
        }

        if ($field_info['visible_form']) {
          $fieldDefinition->setDisplayOptions('form', [
            'type' => 'text_textfield',
            'weight' => $weight,
          ]);
        }
        break;
    }

    // Check if we don't have the field type defined yet.
    if (empty($fieldDefinition)) {
      throw new \RuntimeException('Field type not defined yet, please contact TA.');
    }

    // phpcs:ignore
    $fieldDefinition->setLabel(new TranslatableMarkup($field_info['label']));

    // Update cardinality with default value if empty.
    $field_info['description'] = empty($field_info['description']) ? 1 : $field_info['description'];
    $fieldDefinition->setDescription($field_info['description']);

    $fieldDefinition->setTranslatable(TRUE);
    if (isset($field_info['translatable']) && $field_info['translatable'] == 0) {
      $fieldDefinition->setTranslatable(FALSE);
    }

    // Update cardinality with default value if empty.
    $field_info['cardinality'] = empty($field_info['cardinality']) ? 1 : $field_info['cardinality'];
    $fieldDefinition->setCardinality($field_info['cardinality']);

    $fieldDefinition->setDisplayConfigurable('form', 1);
    $fieldDefinition->setDisplayConfigurable('view', 1);

    return $fieldDefinition;
  }

}
