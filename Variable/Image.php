<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Entity\Content;

/**
 * A variable type to handle file upload
 */
class Image extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function buildForm($builder, $name, $variable)
    {
        $builder
            ->add($name, 'file', array(
                'data_class' => null,
                'required' => false,
                'attr' => array(
                    'class' => 'form-control'
                    )
                ))
            ;
    }

    public function resolve($variable)
    {
        return $variable->getImage();
    }

    public function saveValue($variable, $content)
    {
        $variable->setImagePath($content);
        $variable->manageFileUpload();
    }

    public function getName()
    {
        return 'image';
    }
}