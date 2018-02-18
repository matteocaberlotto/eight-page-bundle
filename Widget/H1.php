<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class H1 extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            'text',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:h1.html.twig';
    }

    public function getName()
    {
        return 'h1';
    }
}