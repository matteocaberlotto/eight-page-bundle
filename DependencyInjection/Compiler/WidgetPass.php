<?php

namespace Eight\PageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class WidgetPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->loadWidgets($container);
        $this->loadVariables($container);
    }

    protected function loadWidgets($container)
    {
        if (!$container->has('widget.provider')) {
            return;
        }

        $provider = $container->findDefinition('widget.provider');

        $taggedServices = $container->findTaggedServiceIds(
            'eight_page.widget'
        );

        foreach ($taggedServices as $id => $tags) {
            $provider->addMethodCall(
                'addWidget',
                array(new Reference($id))
            );
        }
    }

    protected function loadVariables($container)
    {
        if (!$container->has('variable.provider')) {
            return;
        }

        $provider = $container->findDefinition('variable.provider');

        $taggedServices = $container->findTaggedServiceIds(
            'eight_page.variable'
        );

        foreach ($taggedServices as $id => $tags) {
            $provider->addMethodCall(
                'addVariable',
                array(new Reference($id))
            );
        }
    }
}