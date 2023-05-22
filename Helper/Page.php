<?php

namespace Eight\PageBundle\Helper;

use Eight\PageBundle\Model\PageInterface;
use Eight\PageBundle\Entity\Block;
use Eight\PageBundle\Entity\Content;
use Eight\PageBundle\Entity\Page as PageEntity;
use Raindrop\RoutingBundle\Entity\Route;

/**
 * Generic helper to perfrom some common database operations.
 */
class Page
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Append a child block to a page or block
     */
    public function append($subject, $id, $name, $slot_label, $static = false)
    {
        $block = new Block();

        if ($static) {
            $nextSeq = $this->container->get('eight.blocks')->getNextStaticPosition($slot_label);
            $block->setStatic(true);
        } else {
            $parent = $this->container->get('doctrine')->getRepository($subject)->find($id);

            switch ($subject) {
                case 'Eight\PageBundle\Entity\Page':
                    $block->setPage($parent);
                    break;
                case 'Eight\PageBundle\Entity\Block':
                    $block->setBlock($parent);
                    break;
                default:
                    throw new \Exception("Class {$subject} not supported");
                    break;
            }

            $nextSeq = $this->container->get('eight.blocks')->getNextPosition($parent, $id, $slot_label);
        }


        $widget = $this->container->get('widget.provider')->get($name);

        $block->setName($name);
        $block->setLayout($widget->getLayout());
        $block->setType($slot_label);
        $block->setSeq($nextSeq);

        $manager = $this->container->get('doctrine')->getManager();
        $manager->persist($block);
        $manager->flush();

        return $block;
    }

    /**
     * Reorder blocks according to list of ids provided.
     */
    public function reorder($ids)
    {
        $index = 0;

        foreach ($ids as $id) {
            $next = $this->container->get('eight.blocks')->find($id);
            $next->setSeq($index);
            $index++;
        }

        $this->container->get('doctrine')->getManager()->flush();
    }

    /**
     * Clone a page to another entity and return it
     */
    public function duplicatePage($page)
    {
        // clone page properties first
        $cloned = new PageEntity();

        $cloned->setTitle($page->getTitle());

        if ($page->getLayout()) {
            $cloned->setLayout($page->getLayout());
        }

        $cloned->setMetasName($page->getMetasName());
        $cloned->setMetasProperty($page->getMetasProperty());
        $cloned->setMetasHttpEquiv($page->getMetasHttpEquiv());

        foreach ($page->getTags() as $tag) {
            $cloned->addTag($tag);
        }

        $manager = $this->container->get('doctrine')->getManager();
        $manager->persist($cloned);

        $manager->flush();

        $route = $page->getRoute();

        $route_clone = clone $route;
        $route_clone->resetId();

        // manually add resolver
        $route_clone->setResolver($this->container->get('raindrop_routing.content_resolver'));

        $route_clone->setPath($route->getPath() . '/cloned');
        $route_clone->setNameFromPath();

        $route_clone->setContent($cloned);
        $cloned->setRoute($route_clone);

        $manager->persist($route_clone);

        $manager->flush();

        // clone page content
        if (count($page->getBlocks())) {
            foreach ($page->getBlocks() as $block) {
                $this->cloneRecursive($cloned, $block);
            }
        }

        return $cloned;
    }

    public function cloneRecursive($parent, $source_block)
    {
        $manager = $this->container->get('doctrine')->getManager();

        $duplicate_block = new Block();

        $duplicate_block->setName($source_block->getName());
        $duplicate_block->setType($source_block->getType());
        $duplicate_block->setStatic($source_block->getStatic());
        $duplicate_block->setSeq($source_block->getSeq());
        $duplicate_block->setLayout($source_block->getLayout());
        $duplicate_block->setEnabled($source_block->getEnabled());

        foreach ($source_block->getContents() as $content) {
            $duplicate_content = new Content();
            $duplicate_content->setName($content->getName());
            $duplicate_content->setType($content->getType());
            $duplicate_content->setContent($content->getContent());
            $duplicate_content->setBlock($duplicate_block);

            $manager->persist($duplicate_content);
        }

        $subject = get_class($parent);

        switch ($subject) {
            case 'Eight\PageBundle\Entity\Page':
                $duplicate_block->setPage($parent);
                break;
            case 'Eight\PageBundle\Entity\Block':
                $duplicate_block->setBlock($parent);
                break;
            default:
                throw new \Exception("Class {$subject} not supported");
                break;
        }

        $manager->persist($duplicate_block);

        $manager->flush();

        // if source block has children, append them to clone
        if (count($source_block->getBlocks())) {
            foreach ($source_block->getBlocks() as $child) {
                $this->cloneRecursive($duplicate_block, $child);
            }
        }
    }

    public function getStaticBlocksAsArray()
    {
        $blocks = $this->container->get('doctrine')->getRepository(Block::class)->getStaticBlocks();
        $return = [];

        foreach ($blocks as $block) {
            $return []= $block->asArray(true);
        }

        return $return;
    }

    public function easyAdminBindRouteToPage($request, $page)
    {
        $query = $request->query->all();
        $requestParams = $request->request->all();
        $url = $requestParams['pages']['url'];

        $manager = $this->container->get('doctrine')->getManager();

        if (!empty($url)) {

            $route = $page->getRoute();

            $controller = !empty($requestParams['pages']['controller']) ? $requestParams['pages']['controller'] : $this->container->getParameter('eight_page.default_controller');

            if ($route && $route->getPath() == $url) {

                // check if locale has changed
                $locale = $requestParams['pages']['locale'];

                if ($locale !== $route->getLocale()) {
                    $route->setLocale($locale);
                    $manager->flush();
                }

                // check if controller has changed
                if ($controller !== $route->getController()) {
                    $route->setController($controller);
                    $manager->flush();
                }

                // make sure page is bound to route
                if (!$route->getContent() || $route->getContent() !== $page) {
                    $route->setContent($page);
                    $manager->flush();
                }

                return;
            }

            if (!$route) {
                $route = new Route();
                $route->setSchemes([]);
                $route->setResolver($this->container->get('raindrop_routing.content_resolver'));
                $route->setPath($url);
                $route->setNameFromPath();

                $route->setController($controller);
                $route->setLocale($requestParams['pages']['locale']);
                $manager->persist($route);
            } else {
                $route->setPath($url);
                $route->setNameFromPath();
                $route->setController($controller);
                $route->setLocale($requestParams['pages']['locale']);
            }

            if (empty($route->getName())) {
                // set "homepage" name when route name is empty ("/" root path)
                $route->setName('homepage');
            }

            $page->setRoute($route);

            $route->setContent($page);

            if ($route->getController() != $controller) {
                $route->setController($controller);
            }

            if ($route->getLocale() != $requestParams['pages']['locale']) {
                $route->setLocale($requestParams['pages']['locale']);
            }

            $manager->flush();
        }
    }
}
