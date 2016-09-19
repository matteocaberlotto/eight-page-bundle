<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Model\ContentInterface;
use Eight\PageBundle\Variable\AbstractVariable;

use Eight\PageBundle\Form\Type\MultipleTextType;

class MultipleLabels extends AbstractVariable
{
    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $builder
            ->add($name, MultipleTextType::class, array(
                'data' => $this->getArray($variable),
                'required' => false,
                ))
            ;
    }

    public function resolve(ContentInterface $variable, $config)
    {
        return json_decode($variable->getContent());
    }

    public function getValue(ContentInterface $variable, $config)
    {
        return $this->resolve($variable, $config);
    }

    public function saveValue(ContentInterface $variable, $content, $config)
    {
        // cleanup empty ones
        foreach ($content as $index => $value) {
            if (empty($value)) {
                unset($content[$index]);
            }
        }

        $variable->setContent(json_encode($content));
    }

    public function getName()
    {
        return 'multiple_labels';
    }

    public function getArray($variable)
    {
        if ($variable) {
            if ($variable->getContent()) {
                return json_decode($variable->getContent());
            }
        }

        return array();
    }
}


