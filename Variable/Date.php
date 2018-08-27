<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Model\ContentInterface;
use Eight\PageBundle\Variable\AbstractVariable;

class Date extends AbstractVariable
{
    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, DateType::class, array(
                'label' => $config->has('label') ? $config->get('label') : $name,
                'data' => $variable ? ($variable->getContent() ? $this->resolve($variable, $config) : $this->getDefaultValue($config)) : $this->getDefaultValue($config),
                ))
            ;
    }

    public function saveValue(ContentInterface $variable, $content, $config)
    {
        if (!empty($content)) {
            $variable->setContent($content->format('Y-m-d'));
        }
    }

    public function resolve(ContentInterface $variable, $config)
    {
        return new \DateTime($variable->getContent());
    }

    public function getName()
    {
        return 'date';
    }
}
