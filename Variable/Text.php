<?php

namespace Eight\PageBundle\Variable;

use Symfony\Component\Form\FormBuilderInterface;

use Eight\PageBundle\Model\ContentInterface;
use Eight\PageBundle\Variable\AbstractVariable;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class Text extends AbstractVariable
{
    public function buildForm(FormBuilderInterface $builder, $name, $config, ContentInterface $variable = null)
    {
        $editor_class_append = ' eight-page-textarea';

        // prevent rich text editor when requested
        if ($config->has('raw') && $config->get('raw')) {
            $editor_class_append = '';
        }

        $builder
            ->add($name, HiddenType::class, [
                'label' => $config->has('label') ? $config->get('label') : $name,
                'required' => false,
                'attr' => [
                    'class' => 'eight-textarea' . $editor_class_append,
                ]])
            ;
    }

    public function getName()
    {
        return 'text';
    }
}
