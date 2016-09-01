<?php

namespace Eight\PageBundle\Variable;

use Eight\PageBundle\Variable\AbstractVariable;

class i18nUrl extends AbstractVariable
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function resolve($variable)
    {
        $locale = $this->getLocale();

        $page = $this->container->get('eight.pages')->findOneByTag($variable->getContent() . '.' . $locale);

        if ($page) {
            return $page->getRoute()->getPath();
        }

        $page = $this->container->get('eight.pages')->findOneByTag($variable->getContent());

        if ($page) {
            return $page->getRoute()->getPath();
        }
    }

    public function getLocale()
    {
        return $this->container->get('request_stack')->getCurrentRequest()->get('_locale');
    }

    public function getName()
    {
        return 'i18n_url';
    }
}
