<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class NavItemExt extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            'label',
            'external_link',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:navitem_ext.html.twig';
    }

    public function getName()
    {
        return 'nav_item_ext';
    }

    public function getLabel()
    {
        return 'Navbar link (ext)';
    }
}