<?php

namespace Eight\PageBundle\Renderer;

use Eight\PageBundle\Model\BlockInterface;

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

    /**
     * Set current page in case of editing, testing or whatever...
     */
    public function setCurrentPage($page)
    {
        $this->current_page = $page;

        $this->getRequest()->request->set('content', $page);
    }

    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * Retrieve current page
     */
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

    /**
     * Renders a single block
     */
    public function renderBlock($block, $page)
    {
        $html = $this->get('templating')->render($this->getTemplate($block), $this->getVariables($block));

        if ($page->editMode()) {
            $html = $this->decorateHtml($html, 'block', $block);
        }

        return $html;
    }

    /**
     * Load block assets.
     * There are 3 positions: head, default, bottom.
     * If no position is provided default is given.
     */
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

    /**
     * Renders all children of the subject (page or block)
     * of given type (type = where to append inside the layout).
     */
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

    /**
     * Append edit forms to the page.
     */
    public function appendEditForms()
    {
        $forms = '';

        $page = $this->getPage();
        foreach ($page->getBlocks() as $block) {
            $forms .= $this->appendForm($block);
        }

        return $forms;
    }

    /**
     * Append single block form.
     */
    public function appendForm($block)
    {
        $forms = '';

        if ($block->loadsVariables()) {
            $form = $this->createFormForBlock($block);
            $forms .= $this->container->get('templating')->render('EightPageBundle:Content:util/form.html.twig', array(
                'form' => $form->createView(),
                ));
        }

        if ($block->hasChildren()) {
            foreach ($block->getBlocks() as $child) {
                $forms .= $this->appendForm($child);
            }
        }

        return $forms;
    }

    /**
     * Appends editor markup and utils to the page html.
     */
    public function appendPageEditor()
    {
        $template = $this->container->getParameter('eight_page.page_append');

        return $this->container->get('templating')->render($template, array(
            'widgets' => $this->get('widget.provider')->all()
            ));
    }

    /**
     * Decorates a page or block with the editor markup.
     */
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

    /**
     * Loads variables from widget and overrides with database values.
     */
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

    /**
     * Loads from database the variables bound to a block.
     */
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

    /**
     * Checks if all assets have been loaded.
     */
    public function checkAssets()
    {
        if (!$this->assetsLoaded) {
            $this->descendBlocksAndLoadAssets();
        }
    }

    /**
     * Descend the page tree and loads all assets.
     */
    public function descendBlocksAndLoadAssets()
    {
        /** this is the case when the bundle is being used
         * to populate assets but no page is present.
         * eg: when using symfony standard pages.
         */
        if (!$this->getPage()) {
            $this->assetsLoaded = true;
            return;
        }

        foreach ($this->getPage()->getOrderedBlocks() as $block) {
            $this->loadAssets($block);
        }

        $this->assetsLoaded = true;
    }

    /**
     * Renders all css.
     * In case of editing mode, it will append the editor assets and admin assets.
     */
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

            foreach ($this->container->getParameter('eight_page.admin_css') as $admin_css) {
                $css []= $admin_css;
            }
        }

        return $this->get('templating')->render('EightPageBundle:Content:util/css.html.twig', array(
            'css' => $css
            ));
    }

    /**
     * This function will render all js as stated by configuration and blocks.
     * This also append page decorator (needed for the frontpage editing) and the
     * block editing forms.
     */
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

            foreach ($this->container->getParameter('eight_page.admin_js') as $admin_js) {
                $js []= $admin_js;
            }
        }

        $html = $this->get('templating')->render('EightPageBundle:Content:util/js.html.twig', array(
            'js' => $js
            ));

        if ($this->editMode()) {
            $html .= $this->appendPageEditor();
            $html .= $this->appendEditForms();
        }

        return $html;
    }

    /**
     * Appends an asset of a given type to the previous list.
     * It will check for existing and avoid duplication.
     */
    public function appendAssets($previous, $type, $defaults)
    {
        $track = array();

        // track defaults first
        foreach ($defaults as $default) {
            $track[$default] = true;
        }

        // track assets already loaded
        foreach ($previous as $prev) {
            $track[$prev] = true;
        }

        $assets = $this->$type;
        $positions = array('head', 'default', 'bottom');

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