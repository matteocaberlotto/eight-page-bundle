<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;

use Eight\PageBundle\Entity\Content;
use Eight\PageBundle\Variable\AbstractVariable;

/**
 * Retrieve a collection by a field/value couple.
 */
class Collection extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function resolve(Content $variable)
    {
        list($class, $field, $value) = $this->getContent($variable);

        if (!empty($value)) {

            if (empty($field)) {
                $field = 'id';
            }

            return $this->container->get('doctrine')->getRepository($class)->findBy(array(
                $field => $value
                ));
        } else {
            return $this->container->get('doctrine')->getRepository($class)->findAll();
        }
    }

    public function getValue(Content $variable)
    {
        return explode(':', $variable->getContent());
    }

    public function saveValue(Content $variable, $content, $config)
    {
        $variable->setContent(implode(':', $content));
    }

    public function getDefaultValue($config)
    {
        if (isset($config['default_value'])) {
            return $config['default_value'];
        }

        return new ArrayCollection();
    }

    public function getName()
    {
        return 'collection';
    }

    public function buildForm(FormBuilderInterface $builder, $name, $config, Content $variable = null)
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