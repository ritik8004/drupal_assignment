<?php

namespace Drupal\alshaya_spc;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Payment Method plugin manager.
 */
class AlshayaSpcPaymentMethodManager extends DefaultPluginManager {

  /**
   * Constructor for AlshayaSpcPaymentMethodManager objects.
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
    parent::__construct('Plugin/SpcPaymentMethod', $namespaces, $module_handler, NULL, 'Drupal\alshaya_spc\Annotation\AlshayaSpcPaymentMethod');

    $this->alterInfo('alshaya_spc_payment_method_info');
    $this->setCacheBackend($cache_backend, 'alshaya_spc_payment_method_plugins');
  }

}
