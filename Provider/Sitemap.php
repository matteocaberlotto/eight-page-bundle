<?php

namespace Eight\PageBundle\Provider;

class Sitemap
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function provide()
    {
        $lastMod = $this->container->getParameter('last_modified');

        $pages = array();

        $locales = $this->container->getParameter('eight_page.locales');

        foreach ($locales as $locale) {
            $pages = array_merge($pages, $this->container->get('eight.pages')->findPagesByLocale($locale));
        }

        $return = array();

        foreach ($pages as $page) {

            $priority = 0.8;
            foreach ($locales as $locale) {
                if($page->hasTag('homepage.' . $locale)) {
                    $priority = 1;
                }
            }

            $return []= array(
                'loc' => substr($page->getRoute()->getPath(), 1),
                'lastmod' => $lastMod,
                'changefreq' => 'monthly',
                'priority' => $priority
            );
        }

        return $return;
    }
}