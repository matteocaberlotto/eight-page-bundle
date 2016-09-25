<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class Html extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html' => new Config(array(
                'type' => 'text',
                'raw' => true,
                ))
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:html.html.twig';
    }

    public function getLabel()
    {
        return 'Raw html block';
    }

    public function getName()
    {
        return 'html';
    }
}