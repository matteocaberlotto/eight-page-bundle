<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Form\Type\IconChoiceType;

class Icon extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function buildForm($builder, $name, $config, $variable = null)
    {
        $builder
            ->add($name, IconChoiceType::class, array(
                'choices' => $this->getChoices(),
                ))
            ;
    }

    protected function getChoices()
    {
        $icons = $this->container->getParameter('eight_icons');

        return array_combine($icons, $icons);
    }

    public function getName()
    {
        return 'icon';
    }
}
