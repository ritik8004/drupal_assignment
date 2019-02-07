<?php

namespace Alshaya\BehatBuild;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class AlshayaConfigValidator implements ConfigurationInterface {

  /**
   * @inheritdoc
   */
  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder->root('config');

    $rootNode
      ->children()
        ->arrayNode('variables')
          ->defaultValue(array())
          // Validate the key starts with "url_, var_ or lang_".
          ->beforeNormalization()
            ->ifTrue(function ($a) {
              $key = key($a);
              return !(substr($key,0, 4) === 'url_')
                     && !(substr($key,0, 4) === 'var_')
                     && !(substr($key,0, 5) === 'lang_');
            })
            ->thenInvalid('Invalid key for "%s", use variable with prefix "url_, var_ or lang_".')
            ->end()
          ->prototype('variable')->end()
        ->end()
        ->arrayNode('tests')
          ->defaultValue(array())
          ->prototype('scalar')->end()
        ->end()
        ->arrayNode('tags')
          ->defaultValue(array())
          ->prototype('scalar')->end()
        ->end();
    return $treeBuilder;
  }
}
