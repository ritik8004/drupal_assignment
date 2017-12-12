<?php

namespace Drupal\alshaya_config\EventSubscriber;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Constructs a new AlshayaConfigSubscriber object.
   *
   * @param string $root
   *   The app root.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler object.
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage object.
   */
  public function __construct($root,
                              ModuleHandlerInterface $moduleHandler,
                              StorageInterface $configStorage) {
    $this->root = $root;
    $this->moduleHandler = $moduleHandler;
    $this->configStorage = $configStorage;
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
    $modules = $this->moduleHandler->getModuleList();
    $overridden_configs = [];
    $overrides_config_folders = [];

    // Check all enabled modules & fetch overridden config names keyed by
    // folder paths.
    foreach ($modules as $module) {
      $folder = $this->root . '/' . $module->getPath() . '/config/override';
      if (file_exists($folder)) {
        $file_storage = new FileStorage($folder);
        $overridden_configs[$folder] = $file_storage->listAll();
      }
    }

    // Simplify the data & group folders by config names.
    foreach ($overridden_configs as $folder => $collections) {
      foreach ($collections as $collection) {
        $overrides_config_folders[$collection][] = $folder;
      }
    }

    // Check if we have an override for the config being saved.
    if (in_array($config->getName(), array_keys($overrides_config_folders))) {
      $config_data = $config->getRawData();
      $overrides_folders = $overrides_config_folders[$config->getName()];

      // Merge the data available with the config & in the override YAML files.
      foreach ($overrides_folders as $folder) {
        $file_storage = new FileStorage($folder);
        if ($override = $file_storage->read($config->getName())) {
          $config_data = NestedArray::mergeDeep($config_data, $override);
        }
      }

      // Re-write the config to make sure the overrides are not lost.
      $this->configStorage->write($config->getName(), $config_data);
      Cache::invalidateTags($config->getCacheTags());
    }
  }

}
