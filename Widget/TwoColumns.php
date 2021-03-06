<?php

namespace Eight\PageBundle\Widget;

class TwoColumns extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:two_columns.html.twig';
    }

    public function getName()
    {
        return 'two_columns';
    }

    public function getLabel()
    {
        return 'Two columns container';
    }
}