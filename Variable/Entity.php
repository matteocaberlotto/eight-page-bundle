<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\Common\Collections\ArrayCollection;

use Eight\PageBundle\Entity\Content;
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

    public function resolve(Content $variable)
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

    public function getValue(Content $variable)
    {
        return $this->resolve($variable);
    }

    public function saveValue(Content $variable, $content, $config)
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
        if (isset($config['default_value'])) {
            return $config['default_value'];
        }

        return new $config['class']();
    }

    public function getName()
    {
        return 'entity';
    }

    public function buildForm(FormBuilderInterface $builder, $name, $config, Content $variable = null)
    {
        $builder
            ->add($name, 'entity', array(
                'class' => $config['class'],
                ))
            ;
    }
}