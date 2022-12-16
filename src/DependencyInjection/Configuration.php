<?php

namespace Fagforbundet\AntiVirusApiClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

  /**
   * @inheritDoc
   */
  public function getConfigTreeBuilder(): TreeBuilder {
    $treeBuilder = new TreeBuilder('fagforbundet_anti_virus_api_client');

    $treeBuilder
      ->getRootNode()
        ->addDefaultsIfNotSet()
        ->children()
          ->scalarNode('oidc_client')->defaultNull()->end()
          ->scalarNode('base_url')->defaultValue('https://api.antivirus.fagforbundet.no')->end()
        ->end()
      ->end();

    return $treeBuilder;
  }

}
