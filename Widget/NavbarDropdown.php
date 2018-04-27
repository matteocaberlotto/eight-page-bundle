<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class NavbarDropdown extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'label',
            'html_classes',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:nav_dropdown.html.twig';
    }

    public function getName()
    {
        return 'nav_dropdown';
    }

    public function getLabel()
    {
        return 'Navbar dropdown';
    }
}