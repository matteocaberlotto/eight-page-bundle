<?php

namespace Eight\PageBundle\Widget;

class Text extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'text' => array('type' => 'text')
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:text.html.twig';
    }

    public function getName()
    {
        return 'text';
    }
}