<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Model\ContentInterface;

class Integer extends AbstractVariable
{
    public function resolve(ContentInterface $variable, $config)
    {
        return (int) $this->getValue($variable, $config);
    }

    public function getName()
    {
        return 'integer';
    }
}
