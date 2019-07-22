<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Model\ContentInterface;

use Eight\PageBundle\Form\Type\ImagePreviewType;

/**
 * A variable type to handle file upload
 */
class File extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, FileType::class, array(
                'label' => $config->has('label') ? $config->get('label') : $name,
                'data_class' => null,
                'required' => false,
                'attr' => array(
                    'class' => 'form-control'
                    ),
                ))
            ;
    }

    public function resolve(ContentInterface $variable, $config)
    {
        return $variable->getImage();
    }

    public function saveValue(ContentInterface $variable, $content, $config)
    {
        if (!$config->has('folder')) {
            $config->set('folder', 'default');
        }

        $variable->setImagePath($content);
        $variable->manageFileUpload($config);
    }

    public function getName()
    {
        return 'file';
    }
}