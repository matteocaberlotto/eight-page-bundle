<?php

namespace Eight\PageBundle\Provider;

class Variable
{
    protected $container;

    protected $_variables = array();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function get($name)
    {
        if (isset($this->_variables[$name])) {
            return $this->_variables[$name];
        }

        throw new \InvalidArgumentException("Variable \"{$name}\" has not been defined or properly added.");
    }

    public function addVariable($variable)
    {
        $this->_variables[$variable->getName()] = $variable;
    }

    public function all()
    {
        return $this->_variables;
    }
}