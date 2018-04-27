<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class NavbarDropdownItem extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            'target_page' => new Config(array(
                'type' => 'entity',
                'class' => 'Eight\PageBundle\Entity\Page',
                )),
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:nav_dropdown_item.html.twig';
    }

    public function getName()
    {
        return 'nav_dropdown_item';
    }

    public function getLabel()
    {
        return 'Dropdown item';
    }
}