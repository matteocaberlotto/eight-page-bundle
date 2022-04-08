<?php

namespace Eight\PageBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

use Symfony\Component\Finder\Finder;

class LoadPagesCommand extends Command
{
    protected static $defaultName = 'eight:load:pages';

    protected function configure()
    {
        $this
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

        return Command::SUCCESS;
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
                    $this->getContainer()->get('eight.page_generator')->createPage($page);

                    $this->getContainer()->get('doctrine')->getManager()->clear();
                }
            } else {
                $this->output->writeln("<comment>File {$file->getRealPath()} seems empty.</comment>");
            }
        }
    }
}