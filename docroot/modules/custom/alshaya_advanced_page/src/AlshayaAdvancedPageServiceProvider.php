<?php

namespace Drupal\alshaya_advanced_page;

use Drupal\alshaya_advanced_page\Routing\AlshayaAdvancedPageRouter;
use Drupal\alshaya_advanced_page\Routing\AlshayaAdvancedPageRouteProvider;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class Alshaya Advanced Page Service Provider.
 */
class AlshayaAdvancedPageServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override the core 'RouteProvider' class.
    $definition = $container->getDefinition('router.route_provider');
    $definition->setClass(AlshayaAdvancedPageRouteProvider::class);

    // Override the core 'Router' class.
    $definition = $container->getDefinition('router.no_access_checks');
    $definition->setClass(AlshayaAdvancedPageRouter::class);
  }

}
