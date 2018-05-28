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
        $pages = array();

        $locales = $this->container->getParameter('eight_page.locales');

        foreach ($locales as $locale) {
            $pages = array_merge($pages, $this->container->get('eight.pages')->findPagesForSitemapByLocale($locale));
        }

        $return = array();

        foreach ($pages as $page) {

            $return []= array(
                'loc' => substr($page->getRoute()->getPath(), 1),
                'lastmod' => $page->getUpdated(),
                'changefreq' => $page->getSitemapChange() ?: 'monthly',
                'priority' => $page->getSitemapPriority() ?: '0.5',
            );
        }

        return $return;
    }
}