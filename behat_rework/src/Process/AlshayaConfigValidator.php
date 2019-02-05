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