<?php

namespace Eight\PageBundle\Widget;

class ThreeColumns extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'classes' => array()
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:three_columns.html.twig';
    }

    public function getName()
    {
        return 'three_columns';
    }

    public function getLabel()
    {
        return 'Three columns container';
    }
}
