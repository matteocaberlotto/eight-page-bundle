<?php

namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Variable\Config\Config;

class PageLink extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            'html_icon_class' => new Config(array(
                'type' => 'icon',
                )),
            'target_page' => new Config(array(
                'type' => 'entity',
                'class' => 'Eight\PageBundle\Entity\Page',
                )),
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:page_link.html.twig';
    }

    public function getName()
    {
        return 'page_link';
    }

    public function getLabel()
    {
        return 'Link to a page';
    }
}