<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class Text extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'text' => new Config(array(
                'type' => 'text'
                ))
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:text.html.twig';
    }

    public function getName()
    {
        return 'text';
    }
}