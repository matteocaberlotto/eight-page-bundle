<?php


namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Widget\WidgetInterface;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractWidget implements WidgetInterface
{
    public function getVars()
    {
        return array();
    }

    public function getLayout()
    {
        return '';
    }

    abstract public function getName();

    public function getLabel()
    {
        return Container::camelize($this->getName());
    }

    public function js()
    {
        return array();
    }

    public function css()
    {
        return array();
    }
}