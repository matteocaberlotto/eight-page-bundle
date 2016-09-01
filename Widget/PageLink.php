<?php

namespace Eight\PageBundle\Widget;

class PageLink extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'classes',
            'page',
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