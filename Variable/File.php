<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Entity\Content;
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

        if (!$config->has('base_upload_folder')) {
            $config->set('base_upload_folder', 'public');
        }

        $variable->setImagePath($content);
        $this->manageFileUpload($config, $variable);
    }

    public function manageFileUpload($config, $variable)
    {
        if ($variable->getImagePath()) {
            $this->uploadImage($config, $variable);
        }
    }

    /**
     * Manages the copying of the file to the relevant place on the server
     */
    protected function uploadImage($config, $variable)
    {
        // the file property can be empty if the field is not required
        if (null === $variable->getImagePath()) {
            return;
        }

        // we use the original file name here but you should
        // sanitize it at least to avoid any security issues
        $filename = md5(uniqid() . mt_rand(0, 10000)) . "." . $variable->getImagePath()->getClientOriginalExtension();

        // move takes the target directory and target filename as params
        $variable->getImagePath()->move(
            $this->getUploadPath($config),
            $filename
        );

        // set the path property to the filename where you've saved the file
        $variable->setContent(Content::CMS_IMAGES_FOLDER . DIRECTORY_SEPARATOR . $config->get('folder') . DIRECTORY_SEPARATOR . $filename);

        // clean up the file property as you won't need it anymore
        $variable->setImagePath(null);
    }

    protected function getUploadPath($config)
    {
        return __DIR__ . "/../../../../" . $config->get('base_upload_folder') . Content::CMS_IMAGES_FOLDER . DIRECTORY_SEPARATOR . $config->get('folder');
    }

    public function getName()
    {
        return 'file';
    }
}