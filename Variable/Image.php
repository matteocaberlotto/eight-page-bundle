<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Entity\Content;

use Eight\PageBundle\Form\Type\ImagePreviewType;

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

    public function buildForm(FormBuilderInterface $builder, $name, $config, Content $variable = null)
    {
        $builder
            ->add($name, 'file', array(
                'data_class' => null,
                'required' => false,
                'attr' => array(
                    'class' => 'form-control'
                    ),
                ))
            ;

        if ($variable) {
            $builder
                ->add($name . '_preview', ImagePreviewType::class, array(
                    'src' => $variable->getImage(),
                ));
        }
    }

    public function resolve(Content $variable)
    {
        return $variable->getImage();
    }

    public function saveValue(Content $variable, $content, $config)
    {
        $variable->setImagePath($content);
        $variable->manageFileUpload();
    }

    public function getName()
    {
        return 'image';
    }
}