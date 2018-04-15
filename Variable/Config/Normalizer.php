<?php

namespace Eight\PageBundle\Variable\Config;

use Eight\PageBundle\Variable\Config\Config;
use Eight\PageBundle\Variable\Config\ConfigInterface;

/**
 * This class is used to "normalize" variables passed from widget
 * to template.
 * Simple labels will be converted to database labels with Config class.
 * Assigned variables are passed as mixed with default value.
 */
class Normalizer
{
    public static function normalize($index, $value)
    {
        if ($value instanceof ConfigInterface) {
            return array($index, $value);
        }

        if (is_numeric($index) && is_string($value)) {
            return array($value, new Config(array(
                'type' => 'label',
                )));
        }

        return array($index, new Config(array(
            'type' => 'mixed',
            'default_value' => $value,
            'edit' => false,
            )));
    }
}