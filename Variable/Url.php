<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Model\ContentInterface;

class Url extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function resolve(ContentInterface $variable)
    {
        $content = unserialize($variable->getContent());
        return $this->container->get('router')->generateUrl($content['url'], $content['variables'], $content['absolute']);
    }

    public function getName()
    {
        return 'url';
    }
}
