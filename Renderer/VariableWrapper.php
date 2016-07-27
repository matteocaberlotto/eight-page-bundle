<?php

namespace Eight\PageBundle\Renderer;

class VariableWrapper
{
    protected $container;

    protected $content;

    public function __construct($container, $content)
    {
        $this->container = $container;
        $this->content = $content;
    }

    public function __toString()
    {
        return $this->container->get('templating')->render('EightPageBundle:Content:util/decorator_variable.html.twig' , array('content' => $this->content));
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->content, $method), $arguments);
    }
}