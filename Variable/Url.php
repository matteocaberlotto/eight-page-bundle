<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;

class Url extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function resolve($variable)
    {
        $content = unserialize($variable->getContent());
        return $this->container->get('router')->generateUrl($content['url'], $content['variables'], $content['absolute']);
    }

    public function getName()
    {
        return 'url';
    }
}
