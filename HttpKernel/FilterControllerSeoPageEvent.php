<?php

namespace Eight\PageBundle\HttpKernel;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eight\PageBundle\Entity\Page;

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

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        /**
         * @TODO: switch to an interface and find more performing
         * way of binding meta.
         */
        if ($request->get('content') instanceof Page) {
            $page = $request->get('content');
            $this->container->get('session')->set('_locale', $page->getLocale());
            $seoPage = $this->get('eight.page.seo');

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

    public function get($service)
    {
        return $this->container->get($service);
    }
}
