<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Entity\Content;
use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Form\Type\IconChoiceType;

class Icon extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, $name, $config, Content $variable = null)
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
