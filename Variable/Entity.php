<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;

use Eight\PageBundle\Model\ContentInterface;
use Eight\PageBundle\Variable\AbstractVariable;

/**
 * Retrieve an entity by a field/value couple.
 */

class Entity extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function resolve(ContentInterface $variable, $config)
    {
        list($class, $field, $value) = explode(':', $variable->getContent());

        if (!empty($value)) {

            if (empty($field)) {
                $field = 'id';
            }

            return $this->container->get('doctrine')->getRepository($class)->findOneBy(array(
                $field => $value
                ));
        } else {
            throw new \Exception("No value was found in content '{$content}'");
        }
    }

    public function getValue(ContentInterface $variable, $config)
    {
        return $this->resolve($variable, $config);
    }

    public function saveValue(ContentInterface $variable, $content, $config)
    {
        $values = array(
            get_class($content),
            'id',
            $content->getId()
        );

        $variable->setContent(implode(':', $values));
    }

    public function getDefaultValue($config)
    {
        if ($config->hasDefault()) {
            return $config->getDefault();
        }

        $class = $config->get('class');

        return new $class();
    }

    public function getName()
    {
        return 'entity';
    }

    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, 'entity', array(
                'class' => $config->get('class'),
                'placeholder' => 'Choose a model',
                'required' => false,
                'data' => $variable ? $this->getValue($variable, $config) : null,
                ))
            ;
    }
}