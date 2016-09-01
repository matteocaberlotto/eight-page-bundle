<?php

namespace Eight\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class IconChoiceType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }
}