<?php

namespace Eight\PageBundle\Widget;

class OneColumn extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'classes',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:one_column.html.twig';
    }

    public function getName()
    {
        return 'one_column';
    }

    public function getLabel()
    {
        return 'One column container';
    }
}