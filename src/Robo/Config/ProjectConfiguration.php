<?php

namespace Acquia\Blt\Robo\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 *
 */
class ProjectConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder->root('recipe');

    // @codingStandardsIgnoreStart
    $rootNode
      ->children()
        ->scalarNode('human_name')
          ->info('The human readable name of the project.')
          ->cannotBeEmpty()
          ->isRequired()
        ->end()
        ->scalarNode('machine_name')
          ->info('The machine readable name of the project.')
          ->cannotBeEmpty()
          ->isRequired()
        ->end()
        ->scalarNode('prefix')
          ->info('The project prefix, used for commit message validation.')
          ->cannotBeEmpty()
          ->isRequired()
        ->end()
        ->scalarNode('profile')
          ->info('The installation profile.')
          ->cannotBeEmpty()
          ->isRequired()
          ->validate()
            ->ifNotInArray(['lightning', 'standard', 'minimal'])
            ->thenInvalid('Invalid installation profile %s')
          ->end()
        ->end()
        ->booleanNode('vm')
          ->isRequired()
        ->end()
        ->arrayNode('ci')
          ->children()
            ->scalarNode('provider')
              ->isRequired()
              ->cannotBeEmpty()
              ->defaultValue('pipelines')
              ->validate()
                ->ifNotInArray(['pipelines', 'travis_ci'])
                ->thenInvalid('Invalid continuous integration provider %s')
              ->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('cm')
          ->children()
            ->scalarNode('strategy')
              ->isRequired()
              ->cannotBeEmpty()
              ->defaultValue('config-split')
              ->validate()
                ->ifNotInArray(['config-split', 'features', 'none'])
                ->thenInvalid('Invalid configuration management strategy %s')
              ->end()
            ->end()
          ->end()
        ->end()
        ->arrayNode('ac')
          ->children()
            ->scalarNode('site')
              ->isRequired()
              ->cannotBeEmpty()
            ->end()
            ->scalarNode('env')
              ->isRequired()
              ->cannotBeEmpty()
              ->defaultValue('dev')
            ->end()
          ->end()
        ->end()
        ->arrayNode('ingredients')
          ->info('A flat array of ingredients, e.g. acquia-blog.')
          ->prototype('scalar')
        ->end()
      ->end();
    // @codingStandardsIgnoreEnd

    return $treeBuilder;
  }

}
