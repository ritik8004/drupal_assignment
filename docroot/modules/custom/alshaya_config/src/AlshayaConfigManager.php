<?php

namespace Drupal\alshaya_config;

use Drupal\Component\Serialization\Yaml as SerializationYaml;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\search_api\Entity\Index;
use http\Exception\InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Alshaya Config Manager.
 *
 * @package Drupal\alshaya_config
 */
class AlshayaConfigManager {

  /**
   * Replace whole config.
   */
  public const MODE_REPLACE = 'replace';

  /**
   * Add only the values missing from config.
   */
  public const MODE_ADD_MISSING = 'missing';

  /**
   * Add missing values recursively from config.
   */
  public const MODE_ADD_MISSING_RECURSIVE = 'missing_recursive';

  /**
   * Merge configs - deep merge.
   */
  public const MODE_MERGE = 'merge';

  /**
   * Replace a particular key in config.
   */
  public const MODE_REPLACE_KEY = 'replace_key';

  /**
   * Just resave existing config and let overrides get applied.
   *
   * This is mainly used for overriding config from CORE or Contrib.
   */
  public const MODE_RESAVE = 'resave';

  /**
   * If there is a replace, we replace the complete configuration.
   */
  public const USE_FROM_REPLACE = 'use_from_replace';

  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Language Manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Theme Manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Module Handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $logger;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * Constructs a new AlshayaConfigManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config storage object.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language Manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   Theme Manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module Handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger Channel Factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              LanguageManagerInterface $language_manager,
                              EntityTypeManagerInterface $entity_type_manager,
                              ThemeManagerInterface $theme_manager,
                              ModuleHandlerInterface $module_handler,
                              LoggerChannelFactoryInterface $logger_factory,
                              FileSystemInterface $file_system) {
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->themeManager = $theme_manager;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger_factory->get('alshaya_config');
    $this->fileSystem = $file_system;
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
   * @param array $options
   *   Array of keys to replace when using MODE_REPLACE_KEY.
   */
  public function updateConfigs(array $configs, $module_name, $path = 'install', $mode = self::MODE_ADD_MISSING, array $options = []) {
    if (empty($configs)) {
      return;
    }

    if (!in_array($path, ['install', 'optional'])) {
      $this->logger->error('If you are trying to apply the overrides, please resave (MODE_RESAVE) original config.');
      throw new InvalidArgumentException('Only original config should be updated.');
    }

    // Skip updating configs for modules currently not installed.
    if (!($this->moduleHandler->moduleExists($module_name))) {
      return;
    }

    foreach ($configs as $config_id) {
      // Be nice to devs, forgive them if they add .yml in config name.
      $config_id = str_replace('.yml', '', $config_id);
      $options['config_name'] = $config_id;

      $config = $this->configFactory->getEditable($config_id);
      $data = $this->getDataFromCode($config_id, $module_name, $path);

      // If block config, replace the theme name with current active theme.
      if (str_starts_with($config_id, 'block.block.')) {
        $data['theme'] = $this->themeManager->getActiveTheme()->getName();
      }

      // If field config.
      if (str_starts_with($config_id, 'field.field.')) {
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
      elseif (str_starts_with($config_id, 'field.storage.')) {
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
          if (isset($data['settings'], $data['settings']['allowed_values']) && !empty($data['settings']['allowed_values'])) {
            $resave_config = TRUE;
            $data['settings']['allowed_values'] = [];
          }

          // Create field storage config.
          FieldStorageConfig::create($data)->save();

          if ($resave_config) {
            // We save it again and now it will go to update config where we
            // do not face issue with allowed values.
            $this->updateConfigs([$config_id], $module_name, $path);
          }
        }
      }
      else {
        $existing = $config->getRawData();
        $existing = is_array($existing) ? $existing : [];
        $updated = $this->getUpdatedData($existing, $data, $mode, $options);
        $config->setData($updated)->save(TRUE);
        $this->configFactory->reset($config_id);
      }

      // Flush image cache for style we updated.
      if (str_starts_with($config_id, 'image.style.')) {
        $style_id = str_replace('image.style.', '', $config_id);

        /** @var \Drupal\image\Entity\ImageStyle $style */
        $style = $this->entityTypeManager->getStorage('image_style')->load($style_id);
        // Using flush() method of ImageStyle entity takes a lot of time as it
        // iterates recursively and deletes each file one by one, deleting
        // the directory using shell cmd is quicker with hook_update.
        $schema = $this->configFactory->get('system.file')->get('default_scheme');
        $directory = file_url_transform_relative(file_create_url($schema . '://styles/' . $style->id()));
        if (file_exists($directory)) {
          $this->logger->info('Removing style directory: @directory.', [
            '@directory' => $directory,
          ]);
          shell_exec(sprintf('rm -rf %s', escapeshellarg(ltrim($directory, '/'))));
        }
        else {
          $this->logger->info('Could not find style directory: @directory to remove.', [
            '@directory' => $directory,
          ]);
        }
      }
      elseif (str_starts_with($config_id, 'search_api.index.')) {
        $index_name = str_replace('search_api.index.', '', $config_id);
        try {
          $index = Index::load($index_name);

          // En-sure we save the index after config change to make sure
          // tables are created properly.
          $index->save();
        }
        catch (\Throwable $e) {
          watchdog_exception('alshaya_config', $e);
        }

        $this->logger->info('Re-saved index @index as config saved.', [
          '@index' => $index_name,
        ]);
      }

      // Add all translations.
      foreach ($this->languageManager->getLanguages() as $language) {
        // Do not translate for default language.
        if ($language->isDefault()) {
          continue;
        }

        $this->updateConfigTranslations($config_id, $language->getId(), $module_name, $path);
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
   * @param array $options
   *   Array of Keys to replace when using MODE_REPLACE_KEY.
   *
   * @return array
   *   Updated data based on mode.
   */
  public function getUpdatedData(array $existing, array $data, $mode, array $options = []) {
    switch ($mode) {
      case self::MODE_ADD_MISSING:
        // For now we check only level one keys. We may want to enhance it
        // later to do recursive check. We may want to complicate this a bit
        // more to handle more scenarios. For now it is simple.
        $data = array_merge($data, $existing);
        break;

      case self::MODE_ADD_MISSING_RECURSIVE:
        // Add Missing keys recursively, Keeping existing data as is.
        $data = NestedArray::mergeDeepArray([$data, $existing], TRUE);
        break;

      case self::MODE_MERGE:
        // Same as $config->merge(). To keep code consistent we do it here.
        $data = NestedArray::mergeDeepArray([$existing, $data], TRUE);
        break;

      case self::MODE_REPLACE_KEY:
        foreach ($options['replace_keys'] as $replace_key) {
          $data_replace_source = $data;
          $data_replace_target = &$existing;
          foreach (explode('.', $replace_key) as $key) {
            $data_replace_source = $data_replace_source[$key];
            $data_replace_target = &$data_replace_target[$key];
          }
          $data_replace_target = $data_replace_source;
        }
        $data = $existing;
        break;

      case self::MODE_RESAVE:
        // We just want the overrides to be applied and not actually change
        // anything in existing config or re-read from config yaml.
        $data = $existing;
        break;

      case self::USE_FROM_REPLACE:
        if (!empty($options['config_name'])) {
          foreach ($this->moduleHandler->getModuleList() as $module) {
            $override_path = drupal_get_path('module', $module->getName()) . '/config/replace/' . $options['config_name'] . '.yml';

            // If there is a replace, we replace the complete configuration.
            if (file_exists($override_path)) {
              $data = Yaml::parse(file_get_contents($override_path));
            }
          }
        }
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

    if (!file_exists($file)) {
      return '';
    }

    return Yaml::parse(file_get_contents($file));
  }

  /**
   * Update Config Translations from code to active storage.
   *
   * @param string $config_id
   *   The name of config to import.
   * @param string $langcode
   *   Language code.
   * @param string $module
   *   Name of the module, where files resides.
   * @param string|null $path
   *   Path where configs reside. Defaults to install.
   */
  public function updateConfigTranslations(string $config_id, string $langcode, string $module, ?string $path = 'install') {
    $path = $langcode . '/' . $path;

    $data = $this->getDataFromCode($config_id, $module, $path);
    if (empty($data)) {
      return;
    }

    /** @var \Drupal\language\Config\LanguageConfigOverride $config */
    $config = $this->languageManager->getLanguageConfigOverride($langcode, $config_id);
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $existing = $config->get($key) ?? [];
        $config->set($key, NestedArray::mergeDeepArray([
          $existing,
          $value,
        ], TRUE));
      }
      else {
        $config->set($key, $value);
      }
    }

    $config->save();
    $this->logger->notice('Saved config translation for language @langcode of @config', [
      '@langcode' => $langcode,
      '@config' => $config_id,
    ]);
  }

  /**
   * Helper function to delete fields.
   *
   * @param string $entity_type
   *   Entity type for which the fields needs to be deleted.
   * @param array $bundles
   *   List of bundles from which the fields need to be deleted.
   * @param array $fields
   *   List of fields that need to be deleted.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteFields($entity_type, array $bundles, array $fields) {
    foreach ($bundles as $bundle) {
      foreach ($fields as $field_name) {
        $field = FieldConfig::loadByName($entity_type, $bundle, $field_name);
        if (!empty($field)) {
          $field->delete();
        }
      }
    }

    foreach ($fields as $field_name) {
      $field_storage = FieldStorageConfig::loadByName($entity_type, $field_name);
      if (!empty($field_storage)) {
        $field_storage->delete();
      }
    }
  }

  /**
   * Helper function to replace YAML overrides for settings.
   *
   * This function only changes the overrides in
   * ~/settings/settings-{brand}{country_code}.yml file.
   *
   * @param string $mdc
   *   The settings value to update in the settings file.
   * @param bool $reset
   *   Whether to reset the config or not.
   *
   * @return int
   *   Returns 1 if values are successfully set else 0.
   */
  public function replaceYamlSettingsOverrides(string $mdc = NULL, $reset = FALSE) {
    // Include overrides.
    $acsf_site_code = mb_strtolower(Settings::get('acsf_site_code'));
    $country_code = mb_strtolower(Settings::get('country_code'));
    $settings_path = Settings::get('settings_override_yaml_file_path');

    $settings_file = $settings_path . '-' . $acsf_site_code . $country_code . '.yml';

    if ($reset) {
      if (file_exists($settings_file)) {
        unlink($settings_file);
        $this->logger->notice('Magento settings override file @file removed.', [
          '@file' => $settings_file,
        ]);
      }

      return 1;
    }

    if (empty($mdc)) {
      $env = mb_strtolower(Settings::get('env_name'));
      if ($env === 'local') {
        $mdc = $acsf_site_code . '_qa';
      }
      else {
        $mdc = $acsf_site_code . '_' . $env;
      }
    }

    // @codingStandardsIgnoreLine
    global $magentos;

    if (empty($magentos[$mdc])) {
      throw new \Exception("MDC code $mdc unknown.");
    }

    $settings = [];
    $settings['algolia_env'] = $magentos[$mdc]['algolia_env'] ?? $mdc;
    $settings['alshaya_api.settings']['magento_host'] = $magentos[$mdc]['url'];

    foreach ($magentos[$mdc]['magento_secrets'] ?? [] as $key => $value) {
      $settings['alshaya_api.settings'][$key] = $value;
    }

    // Reset magento_lang_prefix - EN and AR.
    foreach ($this->languageManager->getLanguages() as $langcode => $language) {
      $settings['alshaya_api.settings']['magento_lang_prefix'][$langcode] = $magentos[$mdc][$country_code]['magento_lang_prefix'][$langcode]
        ?? $magentos['default'][$country_code]['magento_lang_prefix'][$langcode];

      $this->logger->notice('Configuring alshaya_api.settings.magento_lang_prefix for @langcode to @value.', [
        '@langcode' => $langcode,
        '@value' => $settings['alshaya_api.settings']['magento_lang_prefix'][$langcode],
      ]);

      $settings['store_id'][$langcode] = $magentos[$mdc][$country_code]['store_id'][$langcode]
        ?? $magentos['default'][$country_code]['store_id'][$langcode];

      $this->logger->notice('Configuring store_id for @langcode to @value.', [
        '@langcode' => $langcode,
        '@value' => $settings['store_id'][$langcode],
      ]);
    }

    $yml_settings = SerializationYaml::encode($settings);
    $settings_dir = dirname($settings_file);
    $this->fileSystem->prepareDirectory($settings_dir, FileSystemInterface::CREATE_DIRECTORY);
    if (file_put_contents($settings_file, $yml_settings)) {
      $this->logger->notice('Configuring alshaya_api.settings.magento_host to @value.', [
        '@value' => $settings['alshaya_api.settings']['magento_host'],
      ]);
      return 1;
    }
    else {
      $this->logger->notice('Failed configuring alshaya_api.settings.magento_host to @value.', [
        '@value' => $settings['alshaya_api.settings']['magento_host'],
      ]);
      return 0;
    }
  }

}
