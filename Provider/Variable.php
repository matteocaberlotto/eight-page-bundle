<?php

namespace Eight\PageBundle\Provider;

use Eight\PageBundle\Entity\Page;
use Doctrine\Common\Collections\ArrayCollection;

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
        return $this->_variables[$name];
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