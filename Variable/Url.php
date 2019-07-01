<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;
use Eight\PageBundle\Model\ContentInterface;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Url extends AbstractVariable
{
    protected $container;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function resolve(ContentInterface $variable, $config)
    {
        $content = unserialize($variable->getContent());
        return $this->router->generateUrl($content['url'], $content['variables'], $content['absolute']);
    }

    public function getName()
    {
        return 'url';
    }
}
