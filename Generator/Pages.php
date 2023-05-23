<?php

namespace Eight\PageBundle\Generator;

use Raindrop\RoutingBundle\Entity\Route;
use Eight\PageBundle\Entity\Page;
use Eight\PageBundle\Entity\Tag;
use Eight\PageBundle\Entity\Block;
use Eight\PageBundle\Entity\Content;

class Pages
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
        $this->doctrine = $this->container->get('doctrine')->getManager();
    }

    /**
     * Creates a page based on data provided.
     */
    public function createPage($data)
    {
        // set a default action if not present
        if (!isset($data['controller'])) {
            $data['controller'] = $this->container->getParameter('eight_page.default_controller');
        }

        $route = $this->doctrine->getRepository(Route::class)->findOneBy(array(
            'path' => $data['url']
        ));

        if (!$route) {
            $route = new Route();
            $route->setSchemes(array());
            $route->setResolver($this->container->get('raindrop_routing.content_resolver'));
            $this->doctrine->persist($route);
        }

        $route->setPath($data['url']);
        $route->setNameFromPath();
        $route->setController($data['controller']);
        $route->setLocale($data['locale']);

        $page = $route->getContent();

        if (!$page instanceof Page) {
            $page = new Page();
            $this->doctrine->persist($page);
        }

        if (isset($data['layout'])) {
            $page->setLayout($data['layout']);
        } else {
            $page->setLayout(null);
        }

        $page->setTitle($data['title']);
        $this->doctrine->flush();

        $page->setRoute($route);
        $route->setContent($page);

        if (count($data['tags'])) {

            foreach ($page->getTags() as $tag) {
                $page->getTags()->remove($tag->getId());
            }
            $this->doctrine->flush();

            $this->createTags($page, $data['tags']);
        }

        $this->doctrine->flush();

        if (isset($data['blocks'])) {

            foreach ($page->getBlocks() as $block) {
                $this->doctrine->remove($block);
            }
            $this->doctrine->flush();

            $this->createBlocks($data['blocks'], $page);
        }

        $this->doctrine->flush();

        if (isset($data['static_blocks'])) {
            $this->createBlocks($data['static_blocks']);
        }

        if (isset($data['published'])) {
            $page->setPublished($data['published']);
        }

        $this->doctrine->flush();

        return $page;
    }

    protected function createTags($page, $tags)
    {
        foreach ($tags as $t) {
            $tag = $this->doctrine->getRepository(Tag::class)->findOneBy(array(
                'name' => $t
            ));

            if (!$tag) {
                $tag = new Tag();
                $tag->setName($t);
                $page->addTag($tag);
                $this->doctrine->persist($tag);
            }

            if (!$page->hasTag($tag->getName())) {
                $page->addTag($tag);
            }
        }
    }

    protected function createBlocks($blocks_data, $parent = null)
    {
        foreach ($blocks_data as $index => $block_data) {

            if (!isset($block_data['type'])) {
                $block_data['type'] = 'default';
            }

            $prev = $this->searchBlock($parent, $block_data['name'], $index, $block_data['type']);

            if (!$prev) {
                $prev = new Block();
                $prev->setName($block_data['name']);
                $prev->setSeq($index);

                if ($parent instanceof Block) {
                    $prev->setBlock($parent);
                } else if ($parent instanceof Page) {
                    $prev->setPage($parent);
                }

                if (!$parent) {
                    $prev->setStatic(true);
                }

                $this->doctrine->persist($prev);
            }

            $prev->setType($block_data['type']);

            $layout = $this->container->get('widget.provider')->get($block_data['name'])->getLayout();
            $prev->setLayout($layout);

            if (isset($block_data['contents'])) {
                $this->createContents($prev, $block_data);
            }

            $prev->setEnabled(true);

            $this->doctrine->flush();

            if (isset($block_data['blocks'])) {
                $this->createBlocks($block_data['blocks'], $prev);
            }
        }
    }

    protected function searchBlock($parent, $name, $position, $type)
    {
        if ($parent instanceof Page) {
            return $this->doctrine->getRepository(Block::class)->findOneBy(array(
                'name' => $name,
                'seq' => $position,
                'page' => $parent,
                'type' => $type,
                ));
        }

        if ($parent instanceof Block) {
            return $this->doctrine->getRepository(Block::class)->findOneBy(array(
                'name' => $name,
                'seq' => $position,
                'block' => $parent,
                'type' => $type,
                ));
        }

        return $this->doctrine->getRepository(Block::class)->findOneBy(array(
            'name' => $name,
            'seq' => $position,
            'static' => true,
            'type' => $type,
            ));
    }

    protected function createContents($block, $data)
    {
        foreach ($data['contents'] as $c) {

            if (!isset($c['type'])) {
                $c['type'] = 'label';
            }

            $content = $this->doctrine->getRepository(Content::class)->findOneBy(array(
                'name' => $c['name'],
                'block' => $block
            ));

            if (!$content) {
                $content = new Content();
                $content->setName($c['name']);
                $content->setBlock($block);

                $this->doctrine->persist($content);
            }

            $content->setType($c['type']);

            if ($c['type'] == 'entity') {

                list($class, $field, $value) = explode(':', $c['content']);

                if (empty($field)) {
                    $field = 'id';
                }

                $c['content'] = $this->container->get('doctrine')->getRepository($class)->findOneBy(array(
                    $field => $value,
                    ));
            }

            if ($c['type'] == 'image') {
                $content->setContent($c['content']);
            } else {
                $this->container->get('variable.provider')->get($c['type'])->saveValue($content, $c['content'], $c);
            }
        }
    }
}