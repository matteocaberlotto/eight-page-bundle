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

            // config array not defined and index is numeric => simple variable
            if (is_numeric($name)) {
                $name = $config;
                $config = array();
            } else {
                // config is an array but it doesnt looks like a config array => declared variable (not to be overridden)
                if (is_array($config) && !isset($config['type']) && !isset($config['default_value']) && !isset($config['edit'])) {
                    $config = array(
                        'default_value' => $config,
                        'edit' => false,
                    );
                }
            }

            if (!is_array($config)) {
                $config = array(
                    'default_value' => $config,
                    'edit' => false,
                    );
            }

            if (!isset($config['type'])) {
                $config['type'] = 'label';
            }

            // if default value is contained into configuration, use it (this is the case of a variable declared inside widget)
            // else try resolution from variable handler (this is the case of a database variable).
            if (isset($config['default_value'])) {
                $vars[$name] = $config['default_value'];
            } else {
                $vars[$name] = $this->container->get('variable.provider')->get($config['type'])->getDefaultValue($config);
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

    /**
     * This method is used only to retrieve database overridden contents.
     */
    public function getConfigFor($widget_name, $variable_name)
    {
        $widget = $this->get($widget_name);

        foreach ($this->getVariables($widget_name) as $name => $config) {
            if (is_numeric($name)) {
                // simple variable (no config)
                if ($config == $variable_name) {
                    return array();
                }
            }

            // configured variable
            if (is_array($config)) {
                return $config;
            }

            return array();
        }

        throw new \InvalidArgumentException("Variable {$variable_name} is not present in the widget {$widget_name}");
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