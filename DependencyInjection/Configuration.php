<?php

namespace Eight\PageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('eight_page');

        $rootNode
            ->children()
                ->scalarNode('use_default_widgets')
                    ->defaultValue(false)
                ->end()
                ->arrayNode('controller_map')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('page_content')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('locales')
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('http_metas')
                    ->prototype('variable')->end()
                ->end()
                ->scalarNode('encoding')->end()
                ->scalarNode('redirect_home')->end()
                ->scalarNode('default_controller')
                    ->defaultValue('EightPageBundle:Default:index')
                ->end()
                ->scalarNode('default_layout')
                    ->defaultValue('EightPageBundle:Default:index.html.twig')
                ->end()
                ->scalarNode('default_edit_layout')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('page_append')
                    ->defaultValue('EightPageBundle:Content:util/page_append.html.twig')
                ->end()
                ->scalarNode('decorator_static')
                    ->defaultValue('EightPageBundle:Content:util/decorator_static.html.twig')
                ->end()
                ->scalarNode('decorator_list')
                    ->defaultValue('EightPageBundle:Content:util/decorator_list.html.twig')
                ->end()
                ->scalarNode('decorator_block')
                    ->defaultValue('EightPageBundle:Content:util/decorator_block.html.twig')
                ->end()
                ->arrayNode('css')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('admin_css')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('js')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('admin_js')
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end()
            ;

        return $treeBuilder;
    }
}
