<?php

namespace Eight\PageBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Yaml\Yaml;

use Raindrop\RoutingBundle\Entity\Route;
use Eight\PageBundle\Entity\Page;
use Eight\PageBundle\Entity\Tag;
use Eight\PageBundle\Entity\Block;
use Eight\PageBundle\Entity\Content;

use Symfony\Component\Finder\Finder;

class LoadPagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eight:load:pages')
            ->setDescription('This command populates database with pages data')
            ->addArgument(
                'bundle',
                InputArgument::REQUIRED,
                'The bundle where to search to source file (pages.<locale>.yml)'
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        $this->input = $input;
        $this->output = $output;
        $this->doctrine = $this->getContainer()->get('doctrine')->getManager();

        $this->createPages();

        $output->writeln('Job done');
        $output->writeln('Elapsed time: ' . (microtime(true) - $start));
    }

    protected function createPages()
    {
        $finder = new Finder();

        $foundBundle = $this->getApplication()->getKernel()->getBundle($this->input->getArgument('bundle'));
        $bundleSourcePath = $foundBundle->getPath().'/Resources/config';

        $files = $finder
            ->files()
            ->name('pages.*.yml')
            ->in($bundleSourcePath)
            ;

        foreach ($files as $file) {

            $this->output->writeln("Parsing file <info>{$file->getRealPath()}</info>");

            $source = Yaml::parse(file_get_contents($file->getRealPath()));

            $filename = $file->getBasename();
            list($dummy, $locale, $ext) = explode('.', $filename);

            if (isset($source['pages']) && count($source['pages'])) {
                foreach ($source['pages'] as $page) {
                    $page['locale'] = $locale;
                    $this->createPage($page);
                }
            } else {
                $this->output->writeln("<comment>File {$file->getRealPath()} seems empty.</comment>");
            }
        }
    }

    protected function createPage($data)
    {
        // set a default action if not present
        if (!isset($data['controller'])) {
            $data['controller'] = $this->getContainer()->getParameter('eight_page.default_controller');
        }

        $route = $this->doctrine->getRepository('RaindropRoutingBundle:Route')->findOneBy(array(
            'path' => $data['url']
        ));

        if (!$route) {
            $route = new Route;
            $route->setSchemes(array());
            $route->setResolver($this->getContainer()->get('raindrop_routing.content_resolver'));
            $this->doctrine->persist($route);
        }

        $route->setPath($data['url']);
        $route->setNameFromPath();
        $route->setController($data['controller']);
        $route->setLocale($data['locale']);

        $page = $route->getContent();

        if (!$page instanceof Page) {
            $page = new Page;
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

        $this->doctrine->clear();

        $this->output->writeln('Completed page <info>' . $page->getTitle() . '</info> <comment>' . $page->getRoute()->getPath() . '</comment>');
    }

    protected function createTags($page, $tags)
    {
        foreach ($tags as $t) {
            $tag = $this->doctrine->getRepository('EightPageBundle:Tag')->findOneBy(array(
                'name' => $t
            ));

            if (!$tag) {
                $tag = new Tag;
                $tag->setName($t);
                $page->addTag($tag);
                $this->doctrine->persist($tag);
            }

            if (!$page->hasTag($tag->getName())) {
                $page->addTag($tag);
            }
        }
    }

    protected function createBlocks($blocks_data, $parent)
    {
        foreach ($blocks_data as $index => $block_data) {

            if (!isset($block_data['type'])) {
                $block_data['type'] = 'default';
            }

            $prev = $this->searchBlock($parent, $block_data['name'], $index, $block_data['type']);

            if (!$prev) {
                $prev = new Block;
                $prev->setName($block_data['name']);
                $prev->setSeq($index);

                if ($parent instanceof Block) {
                    $prev->setBlock($parent);
                } else if ($parent instanceof Page) {
                    $prev->setPage($parent);
                }

                $this->doctrine->persist($prev);
            }

            $prev->setType($block_data['type']);

            $layout = $this->getContainer()->get('widget.provider')->get($block_data['name'])->getLayout();
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
            return $this->doctrine->getRepository('EightPageBundle:Block')->findOneBy(array(
                'name' => $name,
                'seq' => $position,
                'page' => $parent,
                'type' => $type,
                ));
        }

        if ($parent instanceof Block) {
            return $this->doctrine->getRepository('EightPageBundle:Block')->findOneBy(array(
                'name' => $name,
                'seq' => $position,
                'block' => $parent,
                'type' => $type,
                ));
        }

        throw new \Exception("Unable to handle block type for " . get_class($parent));
    }

    protected function createContents($block, $data)
    {
        foreach ($data['contents'] as $c) {

            if (!isset($c['type'])) {
                $c['type'] = 'label';
            }

            $content = $this->doctrine->getRepository('EightPageBundle:Content')->findOneBy(array(
                'name' => $c['name'],
                'block' => $block
            ));

            if (!$content) {
                $content = new Content;
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

                $c['content'] = $this->getContainer()->get('doctrine')->getRepository($class)->findOneBy(array(
                    $field => $value,
                    ));
            }

            $this->getContainer()->get('variable.provider')->get($c['type'])->saveValue($content, $c['content']);
        }
    }
}