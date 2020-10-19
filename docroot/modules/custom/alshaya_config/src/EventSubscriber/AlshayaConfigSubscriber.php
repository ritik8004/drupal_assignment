<?php

namespace Drupal\alshaya_config\EventSubscriber;

use Drupal\alshaya_config\AlshayaArrayUtils;
use Drupal\Component\Utility\DiffArray;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\language\Config\LanguageConfigOverrideCrudEvent;
use Drupal\language\Config\LanguageConfigOverrideEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Alshaya Config Subscriber.
 */
class AlshayaConfigSubscriber implements EventSubscriberInterface {

  /**
   * Static flag to avoid recursive execution for event.
   *
   * @var bool
   */
  protected static $processingOnLanguageConfigOverrideSave = FALSE;

  /**
   * Module handler object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Alshaya array utility service.
   *
   * @var \Drupal\alshaya_config\AlshayaArrayUtils
   */
  protected $alshayaArrayUtils;

  /**
   * User account object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;
  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a new AlshayaConfigSubscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler object.
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config Factory.
   * @param \Drupal\alshaya_config\AlshayaArrayUtils $alshaya_array_utils
   *   Alshaya array utility service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   User account object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   Logger factory.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler,
                              StorageInterface $configStorage,
                              ConfigFactoryInterface $configFactory,
                              AlshayaArrayUtils $alshaya_array_utils,
                              AccountProxyInterface $account,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->moduleHandler = $moduleHandler;
    $this->configStorage = $configStorage;
    $this->configFactory = $configFactory;
    $this->alshayaArrayUtils = $alshaya_array_utils;
    $this->account = $account;
    $this->logger = $logger_factory->get('alshaya_config');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE] = ['onConfigSave'];
    $events[LanguageConfigOverrideEvents::SAVE_OVERRIDE] = ['onLanguageConfigOverrideSave'];

    return $events;
  }

  /**
   * This method is called whenever the config.save event is fired.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Response event Object.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    $config_name = $config->getName();
    $data = $config->getRawData();
    $override_deletions = [];

    // Override the config data with module overrides.
    $this->fetchOverrides($data, 'override', $config_name);

    // Get delete-overrides from enabled modules.
    $this->fetchOverrides($override_deletions, 'override-delete', $config_name);
    if (!empty($override_deletions)) {
      // Get recursive diff of config data with delete-overrides.
      $data = DiffArray::diffAssocRecursive($data, $override_deletions);
    }

    // Allow other modules to alter the data.
    $this->moduleHandler->alter('alshaya_config_save', $data, $config_name);

    // Re-write the config to make sure the overrides are not lost.
    $this->configStorage->write($config->getName(), $data);
    Cache::invalidateTags($config->getCacheTags());
    $this->configFactory->reset($config_name);

    // Log the config changes.
    if ($this->configFactory->get('alshaya_config.settings')->get('log_config_changes')) {
      $this->logConfigChanges($config_name, $config->getOriginal(), $data);
    }
  }

  /**
   * This method is called whenever the config language override is saved.
   *
   * @param \Drupal\language\Config\LanguageConfigOverrideCrudEvent $event
   *   Event Object.
   */
  public function onLanguageConfigOverrideSave(LanguageConfigOverrideCrudEvent $event) {
    if (static::$processingOnLanguageConfigOverrideSave) {
      return;
    }

    $config = $event->getLanguageConfigOverride();
    $config_name = $config->getName();

    $data_modified = FALSE;
    // We browse all the modules to check for override.
    foreach ($this->moduleHandler->getModuleList() as $module) {
      $override_path = drupal_get_path('module', $module->getName()) . '/config/' . $config->getLangcode() . '/override/' . $config_name . '.yml';

      // If there is an override, we merge it with the initial config.
      if (file_exists($override_path)) {
        $data_modified = TRUE;
        $config->merge(Yaml::parse(file_get_contents($override_path)));

        // Disable subscription of this event for now.
        static::$processingOnLanguageConfigOverrideSave = TRUE;

        $config->save();

        // Enable again.
        static::$processingOnLanguageConfigOverrideSave = FALSE;
      }
    }

    if ($data_modified) {
      // Re-write the config to make sure the overrides are not lost.
      Cache::invalidateTags($config->getCacheTags());
      $this->configFactory->reset($config_name);

      // Log the config changes.
      if ($this->configFactory->get('alshaya_config.settings')->get('log_config_changes')) {
        $this->logger->notice('Config overrides appled for @config in @language', [
          '@config' => $config_name,
          '@language' => $config->getLangcode(),
        ]);
      }
    }
  }

  /**
   * Helper function to fetch overrides from all modules.
   *
   * @param array $data
   *   Intial data variable that needs to be overridden.
   * @param string $override_type
   *   Override type: override/delete-override.
   * @param string $config_name
   *   Name of config for which we checking for overrides.
   */
  public function fetchOverrides(array &$data, $override_type, $config_name) {
    $data_modified = FALSE;
    // We browse all the modules to check for override.
    foreach ($this->moduleHandler->getModuleList() as $module) {
      $override_path = drupal_get_path('module', $module->getName()) . '/config/' . $override_type . '/' . $config_name . '.yml';

      // If there is an override, we merge it with the initial config.
      if (file_exists($override_path)) {
        $data_modified = TRUE;
        $override = Yaml::parse(file_get_contents($override_path));
        $data = NestedArray::mergeDeep($data, $override);
      }
    }

    // Remove duplicates in indexed arrays only if we have modified.
    if ($data_modified && is_array($data)) {
      $this->alshayaArrayUtils->arrayUnique($data);
    }
  }

  /**
   * Logs the config diff when config is saved/updated.
   *
   * @param string $config_name
   *   Config name.
   * @param array $old_config
   *   Old config value.
   * @param array $new_config
   *   New config value.
   */
  protected function logConfigChanges(string $config_name, array $old_config = [], array $new_config = []) {
    static $config_logged = [];
    // Do not log the message multiple times for the same config change.
    if (empty($config_logged[$config_name])) {
      // If user email no available, means config is saved via drush.
      $current_user_mail = $this->account->getEmail() ?? 'Drush';
      $this->logger->info('Config: @config updated by @user. Old config: @old. New config: @new', [
        '@config' => $config_name,
        '@user' => $current_user_mail,
        '@old' => json_encode($old_config),
        '@new' => json_encode($new_config),
      ]);
      $config_logged[$config_name] = $config_name;
    }
  }

}
