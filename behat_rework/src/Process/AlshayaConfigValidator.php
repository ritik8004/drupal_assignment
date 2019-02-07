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
          // Validate the key starts with "url_, var_ or lang_".
          ->beforeNormalization()
            ->ifTrue(function ($a) {
              $key = key($a);
              return !(substr($key, 0, 4) === 'url_')
                     && !(substr($key, 0, 4) === 'var_')
                     && !(substr($key, 0, 5) === 'lang_');
            })
            ->thenInvalid('Invalid key for "%s", use variable with prefix "url_, var_ or lang_".')
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
