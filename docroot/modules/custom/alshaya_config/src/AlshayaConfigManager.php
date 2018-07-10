<?php

namespace Drupal\alshaya_config;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AlshayaConfigManager.
 *
 * @package Drupal\alshaya_config
 */
class AlshayaConfigManager {

  /**
   * Replace whole config.
   */
  const MODE_REPLACE = 'replace';

  /**
   * Add only the values missing from config.
   */
  const MODE_ADD_MISSING = 'missing';

  /**
   * Merge configs - deep merge.
   */
  const MODE_MERGE = 'merge';

  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AlshayaConfigManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config storage object.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Update Config from code to active storage.
   *
   * @param array $configs
   *   The name of configs to import.
   * @param string $module_name
   *   Name of the module, where files resides.
   * @param string $path
   *   Path where configs reside. Defaults to install.
   * @param string $mode
   *   Mode of update operation replace / add missing.
   */
  public function updateConfigs(array $configs, $module_name, $path = 'install', $mode = self::MODE_REPLACE) {
    if (empty($configs)) {
      return;
    }

    foreach ($configs as $config_id) {
      // Be nice to devs, forgive them if they add .yml in config name.
      $config_id = str_replace('.yml', '', $config_id);

      $config = $this->configFactory->getEditable($config_id);
      $data = $this->getDataFromCode($config_id, $module_name, $path);

      // If field config.
      if (strpos($config_id, 'field.field.') === 0) {
        $field = FieldConfig::loadByName(
          $data['entity_type'], $data['bundle'], $data['field_name']
        );
        if ($field instanceof FieldConfig) {
          // Update config using config factory.
          $config->setData($data)->save();

          // Load field config again and save again.
          $field = FieldConfig::loadByName(
            $data['entity_type'], $data['bundle'], $data['field_name']
          );
          $field->save();
        }
        // Create field config.
        else {
          FieldConfig::create($data)->save();
        }
      }
      // If field storage.
      elseif (strpos($config_id, 'field.storage.') === 0) {
        $field_storage = FieldStorageConfig::loadByName($data['entity_type'], $data['field_name']);
        if ($field_storage instanceof FieldStorageConfig) {
          $config->setData($data)->save();

          // Load field config again and save again.
          $field_storage = FieldStorageConfig::loadByName($data['entity_type'], $data['field_name']);
          $field_storage->save();
        }
        else {
          $resave_config = FALSE;

          // Some issue with array conversion in allowed values, we handle
          // exception with workaround for now.
          if (isset($data['settings'], $data['settings']['allowed_values'])) {
            $resave_config = TRUE;
            $data['settings']['allowed_values'] = [];
          }

          // Create field storage config.
          FieldStorageConfig::create($data)->save();

          if ($resave_config) {
            // We save it again and now it will go to update config where we
            // do not face issue with allowed values.
            $this->updateConfigs([$config], $module_name, $path);
          }
        }
      }
      else {
        $existing = $config->getRawData();
        $existing = is_array($existing) ? $existing : [];
        $updated = $this->getUpdatedData($existing, $data, $mode);
        $config->setData($updated)->save(TRUE);
      }

      // Flush image cache for style we updated.
      if (strpos($config_id, 'image.style.') === 0) {
        $style_id = str_replace('image.style.', '', $config_id);

        /** @var \Drupal\image\Entity\ImageStyle $style */
        $style = $this->entityTypeManager->getStorage('image_style')->load($style_id);
        $style->flush();
      }
    }
  }

  /**
   * Get updated data to store in config storage.
   *
   * Use mode as AlshayaConfigManager::MODE_ADD_MISSING if you want to add only
   * the newly added configuration values (defaults).
   *
   * @param array $existing
   *   Existing value from config storage.
   * @param array $data
   *   Config data from code.
   * @param string $mode
   *   Mode to use replace/merge.
   *
   * @return array
   *   Updated data based on mode.
   */
  public function getUpdatedData(array $existing, array $data, $mode) {
    switch ($mode) {
      case self::MODE_ADD_MISSING:
        // For now we check only level one keys. We may want to enhance it
        // later to do recursive check. We may want to complicate this a bit
        // more to handle more scenarios. For now it is simple.
        $data = array_merge($data, $existing);
        break;

      case self::MODE_MERGE:
        // Same as $config->merge(). To keep code consistent we do it here.
        $data = NestedArray::mergeDeepArray([$existing, $data], TRUE);
        break;

      case self::MODE_REPLACE:
      default:
        // Do nothing.
        break;
    }

    return $data;
  }

  /**
   * Get config data stored in config files inside code.
   *
   * @param string $config_id
   *   Configuration ID.
   * @param string $module_name
   *   Name of the module, where files resides.
   * @param string $path
   *   Path where configs reside. Defaults to install.
   *
   * @return mixed
   *   Array from YAML file.
   */
  public function getDataFromCode($config_id, $module_name, $path) {
    $file = drupal_get_path('module', $module_name) . '/config/' . $path . '/' . $config_id . '.yml';
    return Yaml::parse(file_get_contents($file));
  }

}
