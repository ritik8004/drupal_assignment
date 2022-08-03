<?php

namespace Alshaya\BehatBuild;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Class AlshayaConfigValidator.
 *
 * @package Alshaya\BehatBuild
 */
class AlshayaConfigValidator implements ConfigurationInterface {

  /**
   * Validate config for variables.
   *
   * @inheritdoc
   */
  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder->root('config');

    $rootNode
      ->children()
        ->arrayNode('variables')
          // Validate the key starts with "url_, var_, product_, lang_, spc_, new_pdp_, algolia_plp_, new_checkout_ or boots_".
          ->beforeNormalization()
            ->ifTrue(function ($a) {
              $key = key($a);
              return !(str_starts_with($key, 'url_'))
                     && !(str_starts_with($key, 'var_'))
                     && !(str_starts_with($key, 'spc_'))
                     && !(str_starts_with($key, 'boots_'))
                     && !(str_starts_with($key, 'product_'))
                     && !(str_starts_with($key, 'new_checkout_'))
                     && !(str_starts_with($key, 'algolia_plp_'))
                     && !(str_starts_with($key, 'new_pdp_'))
                     && !(str_starts_with($key, 'lang_'));
            })
            ->thenInvalid('Invalid key for "%s", use variable with prefix "url_, var_, product_, lang_, spc_, new_pdp_, algolia_plp_, new_checkout_ or boots_".')
            ->end()
          ->prototype('variable')->end()
        ->end()
        ->arrayNode('tests')
          ->prototype('scalar')->end()
        ->end()
        ->arrayNode('tags')
          ->prototype('scalar')->end()
        ->end();
    return $treeBuilder;
  }

}
