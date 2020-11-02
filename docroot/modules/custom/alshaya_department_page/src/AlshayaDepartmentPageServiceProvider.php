<?php

namespace Drupal\alshaya_department_page;

use Drupal\alshaya_department_page\Routing\AlshayaDepartmentPageRouter;
use Drupal\alshaya_department_page\Routing\AlshayaDepartmentPageRouteProvider;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class Alshaya Department Page Service Provider.
 */
class AlshayaDepartmentPageServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override the core 'RouteProvider' class.
    $definition = $container->getDefinition('router.route_provider');
    $definition->setClass(AlshayaDepartmentPageRouteProvider::class);

    // Override the core 'Router' class.
    $definition = $container->getDefinition('router.no_access_checks');
    $definition->setClass(AlshayaDepartmentPageRouter::class);
  }

}
