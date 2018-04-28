<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class H1 extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'heading' => new Config(array(
                'default_value' => 'h1',
                'type' => 'choices',
                'choices' => array(
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                    'h5' => 'h5',
                    'h6' => 'h6',
                    )
                )),
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