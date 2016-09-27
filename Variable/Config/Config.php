<?php

namespace Eight\PageBundle\Variable\Config;

/**
 * Wrap configuration array inside convenient class.
 */
class Config implements ConfigInterface
{
    protected $config;

    public function __construct(array $config = array())
    {
        if (!isset($config['type'])) {
            $config['type'] = 'label';
        }

        $this->config = $config;
    }

    public function has($index)
    {
        return isset($this->config[$index]);
    }

    public function get($index)
    {
        if (isset($this->config[$index])) {
            return $this->config[$index];
        }

        return null;
    }

    public function set($index, $value)
    {
        $this->config[$index] = $value;
    }

    public function editable()
    {
        if (isset($this->config['edit']) && $this->config['edit'] === false) {
            return false;
        }

        return true;
    }

    public function hasDefault()
    {
        return isset($this->config['default_value']);
    }

    public function getDefault()
    {
        if ($this->hasDefault()) {
            return $this->config['default_value'];
        }

        return null;
    }

    public function getType()
    {
        return isset($this->config['type']) ? $this->config['type'] : 'label';
    }
}