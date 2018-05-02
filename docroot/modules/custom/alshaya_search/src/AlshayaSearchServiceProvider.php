<?php

namespace Drupal\alshaya_search;

use Drupal\alshaya_search\FacetManager\AlshayaSearchFacetManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class AlshayaSearchServiceProvider.
 */
class AlshayaSearchServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override the contrib Facet Manager class.
    $definition = $container->getDefinition('facets.manager');
    $definition->setClass(AlshayaSearchFacetManager::class);
  }

}
