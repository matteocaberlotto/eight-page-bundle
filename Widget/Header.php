<?php

namespace Eight\PageBundle\Widget;

class Header extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'brand',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:header.html.twig';
    }

    public function getName()
    {
        return 'header';
    }
}