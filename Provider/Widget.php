<?php

namespace Eight\PageBundle\Provider;

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

        throw new InvalidArgumentException("Widget \"{$name}\" has not been defined or properly added.");
    }

    public function addWidget($widget)
    {
        $this->_widgets[$widget->getName()] = $widget;
    }

    public function all()
    {
        return $this->_widgets;
    }

    public function getDefaultVariables($name)
    {
        $vars = array();

        foreach ($this->_widgets[$name]->getVars() as $name => $config) {
            if (!is_array($config)) {
                $name = $config;
                $config = array(
                    'type' => 'label',
                );
            }

            if (!isset($config['type'])) {
                $config['type'] = 'label';
            }

            $vars[$name] = $this->container->get('variable.provider')->get($config['type'])->getDefaultValue($config);
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

        foreach ($this->getVariables($widget_name) as $name => $variable) {
            if (is_array($variable)) {
                if ($name == $variable_name) {
                    return isset($variable['type']) ? $variable['type'] : '';
                }
            } else {
                if ($variable == $variable_name) {
                    return isset($variable['type']) ? $variable['type'] : '';
                }
            }
        }

        return false;
    }

    public function getConfigFor($widget_name, $variable_name)
    {
        $widget = $this->get($widget_name);

        foreach ($this->getVariables($widget_name) as $name => $variable) {
            if (is_array($variable)) {
                if ($name == $variable_name) {
                    return $variable;
                }
            } else {
                if ($variable == $variable_name) {
                    return array();
                }
            }
        }

        throw new Exception("Variable {$variable_name} is not present in the widget {$widget_name}");
    }

    public function editable($block)
    {
        foreach ($this->_widgets[$block->getName()]->getVars() as $name => $config) {
            if (!isset($config['edit']) || $config['edit'] !== false) {
                return true;
            }
        }

        return false;
    }
}