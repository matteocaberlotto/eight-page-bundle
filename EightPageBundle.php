<?php

namespace Eight\PageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Eight\PageBundle\DependencyInjection\Compiler\WidgetPass;

class EightPageBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new WidgetPass());

        $container->loadFromExtension('twig', array(
            'form_themes' => array(
                'EightPageBundle:Form:fields.html.twig',
            ),
        ));
    }
}
