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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AlshayaConfigSubscriber.
 */
class AlshayaConfigSubscriber implements EventSubscriberInterface {

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
   */
  public function __construct(ModuleHandlerInterface $moduleHandler,
                              StorageInterface $configStorage,
                              ConfigFactoryInterface $configFactory,
                              AlshayaArrayUtils $alshaya_array_utils) {
    $this->moduleHandler = $moduleHandler;
    $this->configStorage = $configStorage;
    $this->configFactory = $configFactory;
    $this->alshayaArrayUtils = $alshaya_array_utils;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE] = ['onConfigSave'];

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

}
