<?php

namespace Drupal\acq_promotion;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\node\NodeInterface;

/**
 * Provides the ACQ Promotion plugin manager.
 */
class AcqPromotionPluginManager extends DefaultPluginManager {

  /**
   * Creates the AcqPromotionPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/AcqPromotion',
      $namespaces,
      $module_handler,
      'Drupal\acq_promotion\AcqPromotionInterface',
      'Drupal\acq_promotion\Annotation\ACQPromotion'
    );

    $this->alterInfo('acq_promotion');
    $this->setCacheBackend($cache_backend, 'acq_promotion_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = [], NodeInterface $promotionNode = NULL) {
    $plugin_definition = $this->getDefinition($plugin_id);
    $plugin_class = DefaultFactory::getPluginClass($plugin_id, $plugin_definition);
    // If the plugin provides a factory method, pass the container to it.
    if (is_subclass_of($plugin_class, 'Drupal\Core\Plugin\ContainerFactoryPluginInterface')) {
      $plugin = $plugin_class::create(\Drupal::getContainer(), $configuration, $plugin_id, $plugin_definition, $promotionNode);
    }
    else {
      $plugin = new $plugin_class($configuration, $plugin_id, $plugin_definition, $promotionNode);
    }

    return $plugin;
  }

}
