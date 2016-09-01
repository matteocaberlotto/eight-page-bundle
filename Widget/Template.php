<?php

namespace Eight\PageBundle\Widget;

class Template extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'template',
            'title',
            'subtitle',
            'description' => array(
                'type' => 'text'
                ),
            'caption',
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