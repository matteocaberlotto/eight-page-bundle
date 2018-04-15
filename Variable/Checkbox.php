<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Model\ContentInterface;
use Eight\PageBundle\Variable\AbstractVariable;

class Checkbox extends AbstractVariable
{
    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, 'checkbox', array(
                'label' => $config->has('label') ? $config->get('label') : $name,
                'data' => $variable ? ($variable->getContent() ? true : false) : false,
                'required' => false,
                ))
            ;
    }
    public function getName()
    {
        return 'checkbox';
    }
}
