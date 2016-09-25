<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class Template extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'template',
            'title',
            'subtitle',
            'description' => new Config(array(
                'type' => 'text'
                )),
            'caption',
        );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:template.html.twig';
    }

    public function getName()
    {
        return 'template';
    }
}