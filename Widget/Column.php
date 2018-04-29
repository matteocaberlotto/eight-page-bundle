<?php

namespace Eight\PageBundle\Widget;

class Column extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:column.html.twig';
    }

    public function getName()
    {
        return 'single_column';
    }

    public function getLabel()
    {
        return 'Single column';
    }
}