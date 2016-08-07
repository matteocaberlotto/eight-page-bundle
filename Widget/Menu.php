<?php

namespace Eight\PageBundle\Widget;

class Menu extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_tag' => array(),
            'html_classes' => array(),
            'html_id' => array(),
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:menu.html.twig';
    }

    public function getName()
    {
        return 'menu';
    }
}