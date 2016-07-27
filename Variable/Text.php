<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;

class Text extends AbstractVariable
{
    public function buildForm($builder, $name, $variable)
    {
        $builder
            ->add($name, 'textarea', array(
                'required' => false,
                'attr' => array(
                    'class' => 'form-control eight-page-textarea'
                    )
                ))
            ;
    }

    public function getName()
    {
        return 'text';
    }
}
