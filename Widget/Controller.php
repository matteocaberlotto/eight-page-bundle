<?php

namespace Eight\PageBundle\Widget;

class Controller extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'controller' => array()
        );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:controller.html.twig';
    }

    public function getName()
    {
        return 'controller';
    }
}