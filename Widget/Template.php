<?php

namespace Eight\PageBundle\Widget;

class Template extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'template' => array(),
            'title' => array(),
            'subtitle' => array(),
            'description' => array(
                'type' => 'text'
                ),
            'caption' => array(),
        );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:template.html.twig';
    }

    public function getName()
    {
        return 'template';
    }
}