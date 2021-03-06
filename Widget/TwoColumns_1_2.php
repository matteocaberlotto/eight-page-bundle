<?php

namespace Eight\PageBundle\Widget;

class TwoColumns_1_2 extends AbstractWidget
{
    public function getVars()
    {
        return array(
            'html_classes',
            );
    }

    public function getLayout()
    {
        return 'EightPageBundle:Widget:two_columns_1_2.html.twig';
    }

    public function getName()
    {
        return 'two_columns_one_two';
    }

    public function getLabel()
    {
        return 'Two columns container (1:2)';
    }
}
