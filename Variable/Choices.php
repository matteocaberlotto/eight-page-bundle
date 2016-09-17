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
                'data' => $variable ? ($variable->getContent() ? $this->getValue($variable, $config) : null) : null,
                'choices' => $config['choices'],
                'expanded' => isset($config['expanded']) ? $config['expanded'] : false,
                'multiple' => isset($config['multiple']) ? $config['multiple'] : false,
                'attr' => array(
                    'class' => 'form-group',
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