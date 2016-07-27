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
    }
}
