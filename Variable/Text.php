<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Entity\Content;
use Eight\PageBundle\Variable\AbstractVariable;

class Text extends AbstractVariable
{
    public function buildForm(FormBuilderInterface $builder, $name, $config, Content $variable = null)
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
