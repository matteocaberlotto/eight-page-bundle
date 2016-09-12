<?php

namespace Eight\PageBundle\Helper;

use Eight\PageBundle\Model\PageInterface;
use Eight\PageBundle\Entity\Block;
use Eight\PageBundle\Entity\Page as PageEntity;


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
    public function append($subject, $id, $name, $slot_label)
    {
        $block = new Block();
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

        $nextSeq = $this->container->get('eight.blocks')->getNextPosition($subject, $id, $slot_label);

        $widget = $this->container->get('widget.provider')->get($name);

        $block->setName($name);
        $block->setLayout($widget->getLayout());
        $block->setType($slot_label);
        $block->setSeq($nextSeq);

        $manager = $this->container->get('doctrine')->getManager();
        $manager->persist($block);
        $manager->flush();
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
        $cloned = new PageEntity();

        $cloned->setTitle($page->getTitle());

        if ($page->getLayout()) {
            $cloned->setLayout($page->getLayout());
        }

        $cloned->setMetasName($page->getMetasName());
        $cloned->setMetasProperty($page->getMetasProperty());
        $cloned->setMetasHttpEquiv($page->getMetasHttpEquiv());

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

        $route->setContent($cloned);
        $cloned->setRoute($route_clone);

        $manager->persist($route_clone);

        $manager->flush();

        return $cloned;
    }
}
