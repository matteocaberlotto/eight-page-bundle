<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Variable\File;
use Eight\PageBundle\Model\ContentInterface;

use Eight\PageBundle\Form\Type\ImagePreviewType;

/**
 * A variable type to handle file upload
 */
class Image extends File
{
    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, 'file', array(
                'label' => $config->has('label') ? $config->get('label') : $name,
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
                    'mapped' => false,
                ));
        }
    }

    public function getName()
    {
        return 'image';
    }
}