<?php

namespace Drupal\alshaya_config;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Alshaya config, config overrides.
 *
 * Reference from config_override module.
 */
class ConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * Module handler object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Cache backend Object.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * Creates a new ModuleConfigOverrides instance.
   *
   * @param string $root
   *   The app root.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler object.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend Object.
   */
  public function __construct($root, ModuleHandlerInterface $moduleHandler, CacheBackendInterface $cacheBackend) {
    $this->root = $root;
    $this->moduleHandler = $moduleHandler;
    $this->cacheBackend = $cacheBackend;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    if ($config = $this->cacheBackend->get('AlshayaConfigOverrider')) {
      $overrides = $config->data;
    }
    else {
      $modules = $this->moduleHandler->getModuleList();

      foreach ($modules as $module) {
        $folder = $this->root . '/' . $module->getPath() . '/config/override';
        if (file_exists($folder)) {
          $file_storage = new FileStorage($folder);
          $overrides = NestedArray::mergeDeep($overrides, $file_storage->readMultiple($file_storage->listAll()));
        }
      }
      $this->cacheBackend->set('AlshayaConfigOverrider', $overrides);
    }

    return array_intersect_key($overrides, array_flip($names));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'AlshayaConfigOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->addCacheTags(['extensions']);
    return $cache_metadata;
  }

}
