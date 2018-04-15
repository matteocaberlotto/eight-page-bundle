<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Model\ContentInterface;
use Eight\PageBundle\Variable\AbstractVariable;

class Choices extends AbstractVariable
{
    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, 'choice', array(
                'label' => $config->has('label') ? $config->get('label') : $name,
                'required' => $config->has('required') ? $config->get('required') : false,
                'data' => $variable ? ($variable->getContent() ? $this->getValue($variable, $config) : $this->getDefaultValue($config)) : $this->getDefaultValue($config),
                'choices' => $config->get('choices'),
                'expanded' => $config->has('expanded') ? $config->get('expanded') : null,
                'multiple' => $config->has('multiple') ? $config->get('multiple') : null,
                'empty_data' => $config->has('empty_data') ? $config->get('empty_data') : null,
                'attr' => array(
                    'class' => $config->get('multiple') ? 'form-group' : 'form-control',
                    ),
                'choice_attr' => array(
                    'class' => 'form-control',
                    ),
                ))
            ;
    }

    public function resolve(ContentInterface $variable, $config)
    {
        return json_decode($variable->getContent());
    }

    public function getValue(ContentInterface $variable, $config)
    {
        return $this->resolve($variable, $config);
    }

    public function saveValue(ContentInterface $variable, $content, $config)
    {
        $variable->setContent(json_encode($content));
    }

    public function getName()
    {
        return 'choices';
    }
}