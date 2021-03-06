<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;

use Eight\PageBundle\Model\ContentInterface;
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

    public function resolve(ContentInterface $variable, $config)
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

    public function getValue(ContentInterface $variable, $config)
    {
        return explode(':', $variable->getContent());
    }

    public function saveValue(ContentInterface $variable, $content, $config)
    {
        $variable->setContent(implode(':', $content));
    }

    public function getDefaultValue($config)
    {
        if ($config->hasDefault()) {
            return $config->getDefault();
        }

        return new ArrayCollection();
    }

    public function getName()
    {
        return 'collection';
    }

    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, null, array(
                'label' => $config->has('label') ? $config->get('label') : $name,
                'required' => false,
                'attr' => array(
                    'class' => 'form-control'
                    )
                ))
            ;
    }
}