<?php

namespace Eight\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * Description of MultipleTextType
 *
 * @author teito
 */
class MultipleTextType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['full_name'] = $view->vars['full_name'].'[]';
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getName()
    {
        return 'multiple_text';
    }
}