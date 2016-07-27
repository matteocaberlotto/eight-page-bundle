<?php


namespace Eight\PageBundle\Widget;

use Eight\PageBundle\Widget\WidgetInterface;

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

    public function getName()
    {
        return null;
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