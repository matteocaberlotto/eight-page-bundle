<?php

namespace Eight\PageBundle\Renderer;

use Eight\PageBundle\Entity\Content;
use Eight\PageBundle\Entity\BlockInterface;

class Page
{
    protected $container, $current_page;

    protected $js = array();
    protected $added_js = array();
    protected $css = array();
    protected $added_css = array();
    protected $assetsLoaded = false;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function setCurrentPage($page)
    {
        $this->current_page = $page;

        $this->getRequest()->request->set('content', $page);
    }

    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    public function getPage()
    {
        if (!$this->current_page) {
            $this->current_page = $this->getRequest()->get('content');
        }

        return $this->current_page;
    }

    public function get($service)
    {
        return $this->container->get($service);
    }

    public function render($page)
    {
        $html = '';

        foreach ($page->getOrderedBlocks('default') as $block) {
            $html .= $this->renderBlock($block, $page);
        }

        if ($page->editMode()) {
            $html = $this->decorateHtml($html, 'list', $page);
        }

        return $html;
    }

    public function renderBlock($block, $page)
    {
        $html = $this->get('templating')->render($this->getTemplate($block), $this->getVariables($block));

        if ($page->editMode()) {
            $html = $this->decorateHtml($html, 'block', $block);
        }

        return $html;
    }

    public function loadAssets($block)
    {
        $widget = $this->get('widget.provider')->get($block->getName());

        $assets = $this->js;
        foreach ($widget->js() as $script => $config) {

            if (!is_array($config)) {
                $script = $config;
                $config = array('position' => 'default');
            }

            if (!isset($config['position'])) {
                $config['position'] = 'default';
            }

            if (!isset($assets[$config['position']])) {
                $assets[$config['position']] = array();
            }

            if (!isset($this->added_js[$script])) {
                $assets[$config['position']] []= $script;
                $this->added_js[$script] = true;
            }

        }
        $this->js = $assets;

        $assets = $this->css;
        foreach ($widget->css() as $script => $config) {

            if (!is_array($config)) {
                $script = $config;
                $config = array('position' => 'default');
            }

            if (!isset($config['position'])) {
                $config['position'] = 'default';
            }

            if (!isset($assets[$config['position']])) {
                $assets[$config['position']] = array();
            }

            if (!isset($this->added_css[$script])) {
                $assets[$config['position']] []= $script;
                $this->added_css[$script] = true;
            }
        }
        $this->css = $assets;

        if ($block->hasChildren()) {
            foreach ($block->getBlocks() as $child) {
                $this->loadAssets($child);
            }
        }
    }

    public function renderBlockChildren($subject, $type)
    {
        $page = $this->getPage();
        $html = '';

        if ($subject instanceof BlockInterface) {
            $children = $subject->getOrderedBlocks($page);
        } else {
            $children = $subject->getOrderedBlocks($type);
        }

        foreach ($children as $sub) {
            if ($sub->getType() == $type) {
                $html .= $this->renderBlock($sub, $page);
            }
        }

        if ($page->editMode()) {
            $html = $this->decorateHtml($html, 'list', $subject, $type);
        }

        return $html;
    }

    public function appendPageEditor()
    {
        $template = $this->container->getParameter('eight_page.page_append');

        return $this->container->get('templating')->render($template, array(
            'widgets' => $this->get('widget.provider')->all()
            ));
    }

    public function decorateHtml($html, $type, $subject, $label = 'default')
    {
        $template = $this->container->getParameter('eight_page.decorator_' . $type);

        $variables = array(
            'html' => $html,
            'type' => $type,
            'subject_class' => get_class($subject),
            'subject' => $subject,
            'id' => $subject->getId(),
            'label' => $label,
            'title' => $subject instanceof BlockInterface ? $subject->getName() : 'main page'
        );

        if ($subject instanceof BlockInterface) {
            $form = $this->createFormForBlock($subject);
            $variables['form'] = $form->createView();
        }

        return $this->get('templating')->render($template, $variables);
    }

    public function createFormForBlock($block)
    {
        return $this->get('eight.form_builder')->build($block);
    }

    public function getTemplate($block)
    {
        return $this->get('layout.provider')->provide($block);
    }

    public function getVariables($block)
    {
        // mark block as editable (when required): renders form in edit page
        if ($this->editMode() && $this->get('widget.provider')->editable($block)) {
            $block->setLoadsVariables();
        }

        $defaults = $this->get('widget.provider')->getDefaultVariables($block->getName());

        $vars = array_merge($defaults, array(
            'current_block' => $block
        ));

        // override with database values

        return array_merge($vars, $this->getDatabaseVariables($block, true));
    }

    public function getDatabaseVariables($block, $resolve = false)
    {
        $vars = array();

        foreach ($block->getContents() as $variable) {
            if ($resolve) {
                // the frontend requires resolved variables (eg: an entity FQDN + id becames the entity itself)
                $vars[$variable->getName()] = $this->container->get('variable.provider')->get($variable->getType())->resolve($variable);
            } else {
                // the backend requires only low level 'resolution' (eg: a serialized array get unserialized).
                // since we store all variables in a single field, we need a bit of hacks.
                $vars[$variable->getName()] = $this->container->get('variable.provider')->get($variable->getType())->getValue($variable);
            }
        }

        return $vars;
    }

    public function checkAssets()
    {
        if (!$this->assetsLoaded) {
            $this->descendBlocksAndLoadAssets();
        }
    }

    public function descendBlocksAndLoadAssets()
    {
        if (!$this->getPage()) {
            $this->assetsLoaded = true;
            return;
        }

        foreach ($this->getPage()->getOrderedBlocks() as $block) {
            $this->loadAssets($block);
        }

        $this->assetsLoaded = true;
    }

    public function css()
    {
        $this->checkAssets();

        $css = array();

        if ($this->editMode()) {
            $css []= '/bundles/eightpage/css/jquery-ui.css';
        }

        $css = $this->appendAssets($css, 'css', $this->container->getParameter('eight_page.css'));

        if ($this->editMode()) {
            $css []= '/bundles/eightpage/css/editor.css';
        }

        return $this->get('templating')->render('EightPageBundle:Content:util/css.html.twig', array(
            'css' => $css
            ));
    }

    public function js()
    {
        $this->checkAssets();

        $js = array();

        if ($this->editMode()) {
            $js []= '/bundles/eightpage/js/jquery-ui.js';
            $js []= '/bundles/eightpage/js/ckeditor/ckeditor.js';
        }

        $js = $this->appendAssets($js, 'js', $this->container->getParameter('eight_page.js'));

        if ($this->editMode()) {
            $js []= '/bundles/eightpage/js/editor.js';
        }

        $html = $this->get('templating')->render('EightPageBundle:Content:util/js.html.twig', array(
            'js' => $js
            ));

        if ($this->editMode()) {
            $html .= $this->appendPageEditor();
        }

        return $html;
    }

    public function appendAssets($previous, $type, $defaults)
    {
        $track = array();

        foreach ($defaults as $default) {
            $track[$default] = true;
        }

        foreach ($previous as $prev) {
            $track[$prev] = true;
        }

        $positions = array('head', 'default', 'bottom');
        $assets = $this->$type;

        foreach ($positions as $position) {
            if (isset($assets[$position])) {
                foreach ($assets[$position] as $asset) {
                    // avoid asset duplication
                    if (!isset($track[$asset])) {
                        $previous []= $asset;
                        $track[$asset] = true;
                    }
                }
            }

            // append page default assets after blocks defaults
            if ($position == 'default') {
                foreach ($defaults as $default) {
                    $previous []= $default;
                }
            }
        }

        return $previous;
    }

    public function editMode()
    {
        $page = $this->getPage();

        if ($page) {
            return $this->getPage()->editMode();
        }

        return false;
    }
}