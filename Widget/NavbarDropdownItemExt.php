<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class NavbarDropdownItemExt extends AbstractWidget
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
        return 'EightPageBundle:Widget:nav_dropdown_item_ext.html.twig';
    }

    public function getName()
    {
        return 'nav_dropdown_item_ext';
    }

    public function getLabel()
    {
        return 'Dropdown item (ext)';
    }
}