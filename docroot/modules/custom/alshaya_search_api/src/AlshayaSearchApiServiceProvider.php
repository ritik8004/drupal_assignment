<?php

namespace Drupal\alshaya_search_api;

use Drupal\alshaya_search_api\Utility\AlshayaPostRequestIndexing;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class Alshaya Search Api Service Provider.
 */
class AlshayaSearchApiServiceProvider extends ServiceProviderBase implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override the core 'PostRequestIndexing' class to add try/catch.
    $definition = $container->getDefinition('search_api.post_request_indexing');
    $definition->setClass(AlshayaPostRequestIndexing::class);
  }

}
