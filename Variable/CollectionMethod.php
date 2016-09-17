<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;

use Eight\PageBundle\Model\ContentInterface;
use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Form\Type\CollectionMethodType;

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

    public function resolve(ContentInterface $variable, $config)
    {
        $config = $this->getValue($variable);
        $method = $config['method'];
        return $this->container->get('doctrine')->getRepository($config['class'])->$method();
    }

    public function getValue(ContentInterface $variable, $config)
    {
        return unserialize($variable->getContent());
    }

    public function saveValue(ContentInterface $variable, $content, $config)
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

    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
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