<?php

namespace Drupal\alshaya_replicate;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Alters replicate ui service for replicate feature.
 */
class AlshayaReplicateServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('replicate_ui.access_check');
    $definition->setClass('Drupal\alshaya_replicate\ReplicateAccessChecker');
    $definition->setArguments(
      [
        new Reference('access_check.permission'),
        new Reference('access_check.entity'),
        new Reference('config.factory'),
      ]
    );
  }

}
