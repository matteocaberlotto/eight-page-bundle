<?php

namespace Eight\PageBundle\Provider;

use Eight\PageBundle\Variable\Config\Normalizer;
use Eight\PageBundle\Variable\Config\ConfigInterface;
use Eight\PageBundle\Variable\Config\Config;

class Widget
{
    protected $container;

    protected $_widgets = array();

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function get($name)
    {
        if (isset($this->_widgets[$name])) {
            return $this->_widgets[$name];
        }

        throw new \InvalidArgumentException("Widget \"{$name}\" has not been defined or properly added.");
    }

    public function addWidget($widget)
    {
        $this->_widgets[$widget->getName()] = $widget;
    }

    public function all()
    {
        return $this->_widgets;
    }

    public function getDefaultVariables($block)
    {
        $vars = array();

        foreach ($this->_widgets[$block->getName()]->getVars($block) as $name => $config) {

            list($name, $config) = Normalizer::normalize($name, $config);

            // if default value is contained into configuration, use it (this is the case of a variable declared inside widget)
            // else try resolution from variable handler (this is the case of a database variable).
            if ($config->hasDefault()) {
                $vars[$name] = $config->getDefault();
            } else {
                $vars[$name] = $this->container->get('variable.provider')->get($config->getType())->getDefaultValue($config);
            }
        }

        return $vars;
    }

    public function getVariables($name)
    {
        return $this->_widgets[$name]->getVars();
    }

    public function getContentType($widget_name, $variable_name)
    {
        $widget = $this->get($widget_name);

        foreach ($this->getVariables($widget_name) as $name => $config) {

            list($name, $config) = Normalizer::normalize($name, $config);

            if ($name == $variable_name) {
                return $config->getType();
            }
        }

        return false;
    }

    /**
     * This method is called when loading database variables and
     * when saving database variables. No "static" variables.
     */
    public function getConfigFor($widget_name, $variable_name)
    {
        $widget = $this->get($widget_name);

        foreach ($this->getVariables($widget_name) as $name => $config) {

            list($name, $config) = Normalizer::normalize($name, $config);

            return $config;
        }

        throw new \InvalidArgumentException("Variable {$variable_name} is not present in the widget {$widget_name}");
    }

    public function editable($block)
    {
        foreach ($this->_widgets[$block->getName()]->getVars() as $name => $config) {

            list($name, $config) = Normalizer::normalize($name, $config);

            if ($config->editable()) {
                return true;
            }
        }

        return false;
    }
}