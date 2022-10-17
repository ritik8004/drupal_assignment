<?php

namespace Drupal\alshaya_rcs_listing;

use Drupal\alshaya_rcs_listing\Routing\AlshayaRcsListingDepartmentPageRouteProvider;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class Alshaya RCS Listing Department Page Service Provider.
 */
class AlshayaRcsListingServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override the core 'RouteProvider' class.
    $definition = $container->getDefinition('router.route_provider');
    $definition->setClass(AlshayaRcsListingDepartmentPageRouteProvider::class);
  }

}
