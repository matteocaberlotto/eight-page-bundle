<?php

namespace Eight\PageBundle\Provider;

use Eight\PageBundle\Entity\Page;

class Layout
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function provide($subject)
    {
        if ($subject->getLayout()) {
            return $subject->getLayout();
        }

        if ($subject instanceof Page) {
            if ($subject->editMode()) {
                if ($this->container->getParameter('eight_page.default_edit_layout')) {
                    return $this->container->getParameter('eight_page.default_edit_layout');
                }
            }

            return $this->container->getParameter('eight_page.default_layout');
        }
    }

    public function provideAll()
    {
        return array(
            'default' => null
            );
    }
}