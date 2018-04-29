<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class ImageText extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'id',
            'html_classes',
            'text' => new Config(array(
                'type' => 'text'
                )),
            'image' => new Config(array(
                'type' => 'image'
                )),
            'image_classes',
            'image_alt',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:image_and_text.html.twig';
    }

    public function getName()
    {
        return 'image_text';
    }
}