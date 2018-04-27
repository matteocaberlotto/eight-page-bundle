<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class NavbarDropdownDivider extends AbstractWidget
{
    public function getLayout()
    {
        return 'EightPageBundle:Widget:nav_dropdown_divider.html.twig';
    }

    public function getName()
    {
        return 'nav_dropdown_divider';
    }

    public function getLabel()
    {
        return 'Navbar dropdown divider';
    }
}