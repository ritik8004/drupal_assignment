<?php

namespace Drupal\alshaya_config\EventSubscriber;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
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
   * Constructs a new AlshayaConfigSubscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler object.
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage object.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler,
                              StorageInterface $configStorage) {
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
    $data = $config->getRawData();

    // We browse all the modules to check for override.
    foreach ($this->moduleHandler->getModuleList() as $module) {
      $override_path = drupal_get_path('module', $module->getName()) . '/config/override/' . $config->getName() . '.yml';

      // If there is an override, we merge it with the initial config.
      if (file_exists($override_path)) {
        $override = Yaml::parse(file_get_contents($override_path));
        $data = NestedArray::mergeDeep($data, $override);
      }
    }

    // Re-write the config to make sure the overrides are not lost.
    $this->configStorage->write($config->getName(), $data);
    Cache::invalidateTags($config->getCacheTags());
  }

}
