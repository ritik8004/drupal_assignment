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
