<?php

namespace Eight\PageBundle\HttpKernel;

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eight\PageBundle\Model\PageInterface;

/**
 * Description of FilterControllerSeoPageEvent
 *
 * @author teito
 */
class FilterControllerSeoPageEvent
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();

        /**
         * Reads seo contents from page and bind to SEO page helper.
         */
        if ($request->get('content') instanceof PageInterface) {
            $page = $request->get('content');
            $this->container->get('session')->set('_locale', $page->getLocale());
            $seoPage = $this->get('eight.page.seo');

            $seoPage->setPage($page);
            $seoPage->setEncoding($this->container->getParameter('eight_page.encoding'));
            $seoPage->setTitle($page->getTitle());
            $seoPage->setDescription($page->getDescription());

            if ($page->getMetasProperty() && count($page->getMetasProperty())) {
                foreach ($page->getMetasProperty() as $name => $content) {
                    if (!empty($content)) {
                        $seoPage->addMeta('property', $name, $content);
                    }
                }
            }

            if ($page->getMetasName() && count($page->getMetasName())) {
                foreach ($page->getMetasName() as $name => $content) {
                    if (!empty($content)) {
                        $seoPage->addMeta('name', $name, $content);
                    }
                }
            }

            if ($page->getMetasHttpEquiv() && count($page->getMetasHttpEquiv())) {
                foreach ($page->getMetasHttpEquiv() as $name => $content) {
                    if (!empty($content)) {
                        $seoPage->addMeta('http-equiv', $name, $content);
                    }
                }
            }
        }

        /**
         * Bind SEO contents in administration page
         */
        if ($request->get('_route') == 'admin_eight_page_page_layout') {
            $seoPage = $this->get('eight.page.seo');

            $seoPage->setEncoding($this->container->getParameter('eight_page.encoding'));

            $page = $this->get('eight.pages')->find($request->get('id'));

            if ($page) {
                $this->container->get('session')->set('_locale', $page->getLocale());


                $seoPage->setPage($page);
                $seoPage->setEncoding($this->container->getParameter('eight_page.encoding'));
                $seoPage->setTitle($page->getTitle());
                $seoPage->setDescription($page->getDescription());

                if (count($page->getMetasProperty())) {
                    foreach ($page->getMetasProperty() as $name => $content) {
                        if (!empty($content)) {
                            $seoPage->addMeta('property', $name, $content);
                        }
                    }
                }

                if (count($page->getMetasName())) {
                    foreach ($page->getMetasName() as $name => $content) {
                        if (!empty($content)) {
                            $seoPage->addMeta('name', $name, $content);
                        }
                    }
                }

                if (count($page->getMetasHttpEquiv())) {
                    foreach ($page->getMetasHttpEquiv() as $name => $content) {
                        if (!empty($content)) {
                            $seoPage->addMeta('http-equiv', $name, $content);
                        }
                    }
                }
            }
        }
    }

    public function get($service)
    {
        return $this->container->get($service);
    }
}
