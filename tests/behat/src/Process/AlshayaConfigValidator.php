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
              return !(substr($key, 0, 4) === 'url_')
                     && !(substr($key, 0, 4) === 'var_')
                     && !(substr($key, 0, 4) === 'spc_')
                     && !(substr($key, 0, 6) === 'boots_')
                     && !(substr($key, 0, 8) === 'product_')
                     && !(substr($key, 0, 13) === 'new_checkout_')
                     && !(substr($key, 0, 12) === 'algolia_plp_')
                     && !(substr($key, 0, 8) === 'new_pdp_')
                     && !(substr($key, 0, 5) === 'lang_');
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
