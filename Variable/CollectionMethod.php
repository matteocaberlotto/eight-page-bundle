<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;
use Doctrine\Common\Collections\ArrayCollection;
use Eight\PageBundle\Form\CollectionMethodType;

/**
 * Retrieve a collection by a class/method couple.
 */
class CollectionMethod extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function resolve($variable)
    {
        $config = $this->getValue($variable);
        $method = $config['method'];
        return $this->container->get('doctrine')->getRepository($config['class'])->$method();
    }

    public function getValue($variable)
    {
        return unserialize($variable->getContent());
    }

    public function saveValue($variable, $content)
    {
        $variable->setContent(serialize($content));
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
        return 'collection_method';
    }

    public function buildForm($builder, $name, $config)
    {
        $builder
            ->add($name, CollectionMethodType::class, array(
                'attr' => array(
                    'class' => ''
                )
            ))
            ;
    }
}