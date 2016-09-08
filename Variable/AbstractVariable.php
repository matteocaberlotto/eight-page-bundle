<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;
use Eight\PageBundle\Model\ContentInterface;

/**
 *
 * This class allows to store a text field in the records
 * inside a block.
 * Tasks:
 * 1. "resolve" the content before it gets passed to template.
 * 2. "unresolve" contents when storing from form post in edit mode.
 * 3. build the field to edit content in admin form.
 */
abstract class AbstractVariable
{
    /**
     * This is performed before the variable gets passed to the template and the admin form
     *
     * @param ContentInterface $variable
     */
    public function resolve(ContentInterface $variable)
    {
        return $this->getValue($variable);
    }

    /**
     * This is performed before saving the value to db.
     *
     * @param ContentInterface $variable
     * @param mixed $content
     * @param array $config
     */
    public function saveValue(ContentInterface $variable, $content, $config)
    {
        $variable->setContent($content);
    }

    /**
     * Returns the raw variable content (eg: for form building)
     *
     * @param ContentInterface $variable
     */
    public function getValue(ContentInterface $variable)
    {
        return $variable->getContent();
    }

    /**
     * Returns the default variable content (if present)
     *
     * @param array $config
     */
    public function getDefaultValue($config)
    {
        if (isset($config['default_value'])) {
            return $config['default_value'];
        }

        return '';
    }

    /**
     * Every variable must have a unique name
     */
    abstract public function getName();

    /**
     * Tells the builder how to edit this variable in admin mode.
     *
     * @param FormBuilderInterface $builder
     * @param strong $name
     * @param array $config
     * @param ContentInterface $variable
     */
    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, null, array(
                'required' => false,
                'attr' => array(
                    'class' => 'form-control'
                    )
                ))
            ;
    }
}