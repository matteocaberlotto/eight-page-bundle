<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class NavItem extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            'label',
            'target_page' => new Config(array(
                'type' => 'entity',
                'class' => 'Eight\PageBundle\Entity\Page',
                )),
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:navitem.html.twig';
    }

    public function getName()
    {
        return 'nav_item';
    }

    public function getLabel()
    {
        return 'Navbar link';
    }
}