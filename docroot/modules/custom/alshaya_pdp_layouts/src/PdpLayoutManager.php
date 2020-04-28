<?php

namespace Drupal\alshaya_pdp_layouts;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the PDP Layouts plugin manager.
 */
class PdpLayoutManager extends DefaultPluginManager {

  /**
   * Default values for each pdp layout plugin.
   *
   * @var array
   */
  protected $defaults = [
    'id' => '',
    'label' => '',
  ];

  /**
   * Constructor for PdpLayoutManager objects.
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
    parent::__construct('Plugin/PdpLayout', $namespaces, $module_handler, 'Drupal\alshaya_pdp_layouts\Plugin\PdpLayout\PdpLayoutInterface', 'Drupal\alshaya_pdp_layouts\Annotation\PdpLayout');

    $this->alterInfo('pdp_layouts_info');
    $this->setCacheBackend($cache_backend, 'pdp_layouts_info_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    foreach (['id', 'label'] as $required_property) {
      if (empty($definition[$required_property])) {
        throw new PluginException(sprintf('The pdp layouts %s must define the %s property.', $plugin_id, $required_property));
      }
    }
  }

  /**
   * Get plugin instance from pdp layout.
   *
   * @param string $pdp_layout
   *   PDP layout.
   *
   * @return object
   *   PDP plugin instance.
   */
  public function getInstanceByLayout($pdp_layout) {
    $plugin_id = 'default';
    if (strpos($pdp_layout, '-') > -1) {
      $split_layout = explode('-', $pdp_layout);
      $plugin_id = end($split_layout);
    }
    $pdp_plugin = $this->createInstance($plugin_id, []);
    return $pdp_plugin;
  }

}
