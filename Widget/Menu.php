<?php

namespace Eight\PageBundle\Widget;

class Menu extends AbstractWidget
{
    public function getVars()
    {
        return array();
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