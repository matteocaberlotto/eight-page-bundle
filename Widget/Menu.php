<?php

namespace Eight\PageBundle\Widget;

class Menu extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_tag',
            'html_classes',
            'html_id',
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