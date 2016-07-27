<?php

namespace Eight\PageBundle\Variable;

abstract class AbstractVariable
{
    public function resolve($variable)
    {
        return $variable->getContent();
    }

    public function saveValue($variable, $content)
    {
        $variable->setContent($content);
    }

    public function getValue($variable)
    {
        return $variable->getContent();
    }

    public function getDefaultValue($config)
    {
        if (isset($config['default_value'])) {
            return $config['default_value'];
        }

        return '';
    }

    abstract public function getName();

    public function buildForm($builder, $name, $config)
    {
        $builder
            ->add($name, null, array(
                'required' => false,
                'attr' => array(
                    'class' => 'form-control'
                    )
                ))
            ;
    }
}