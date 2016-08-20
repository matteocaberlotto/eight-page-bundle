<?php

namespace Eight\PageBundle\Variable;

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
     * This is performed before the variable gets passed to template
     */
    public function resolve($variable)
    {
        return $variable->getContent();
    }

    /**
     * This is performed before saving the value to db.
     */
    public function saveValue($variable, $content)
    {
        $variable->setContent($content);
    }

    /**
     * Returns the raw variable content
     */
    public function getValue($variable)
    {
        return $variable->getContent();
    }

    /**
     * Returns the default variable content (if present)
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
     */
    public function buildForm($builder, $name, $config)
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