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
    public function renderBlock($block, $edit_mode = false)
    {
        $html = $this->get('twig')->render($this->getTemplate($block), $this->getVariables($block));

        if ($edit_mode) {
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
     * of given type (slot_label = where to append inside the layout).
     */
    public function renderBlockChildren($subject, $slot_label)
    {
        $editMode = $this->editMode();
        $html = '';

        if ($subject) {
            if ($subject instanceof BlockInterface) {
                $children = $subject->getOrderedBlocks($editMode);
            } else {
                $children = $subject->getOrderedBlocks($slot_label);
            }

            foreach ($children as $sub) {
                if ($sub->getType() == $slot_label) {
                    $html .= $this->renderBlock($sub, $editMode);
                }
            }
        }

        if ($editMode) {
            $html = $this->decorateHtml($html, 'list', $subject, $slot_label);
        }

        return $html;
    }

    /**
     * Renders all static blocks of given type
     * (slot_label = where to append inside the layout).
     */
    public function renderStaticBlocks($slot_label)
    {
        $page = $this->getPage();

        $html = '';

        $children = $this->getStaticBlocks($slot_label, !$this->editMode());

        foreach ($children as $child) {
            $html .= $this->renderBlock($child, $this->editMode());
        }

        if ($this->editMode()) {
            $html = $this->decorateHtml($html, 'static', null, $slot_label);
        }

        return $html;
    }

    public function getStaticBlocks($slot_label = null, $enabled = false)
    {
        return $this->container->get('eight.blocks')->getStatic($slot_label, $enabled);
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

        foreach ($this->getStaticBlocks() as $block) {
            $forms .= $this->appendForm($block);
        }

        return $forms;
    }

    /**
     * Append single block form.
     */
    public function appendForm($block, $recursive = true)
    {
        $forms = '';

        if ($block->loadsVariables()) {
            $form = $this->createFormForBlock($block);
            $forms .= $this->container->get('twig')->render('@EightPage/Content/util/form.html.twig', array(
                'form' => $form->createView(),
                'current_form_block' => $block,
                ));
        }

        if ($recursive) {
            if ($block->hasChildren()) {
                foreach ($block->getBlocks() as $child) {
                    $forms .= $this->appendForm($child);
                }
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

        return $this->container->get('twig')->render($template, array(
            'page' => $this->getPage(),
            'update_add_buttons_position' => $this->container->getParameter('eight_page.update_add_buttons_position'),
            'widgets' => $this->get('widget.provider')->all(),
            ));
    }

    /**
     * Decorates a page or block with the editor markup.
     */
    public function decorateHtml($html, $type, $subject, $slot_label = 'default')
    {
        $template = $this->container->getParameter('eight_page.decorator_' . $type);

        $variables = array(
            'html' => $html,
            'type' => $type,
            'subject_class' => $subject ? get_class($subject) : null,
            'subject' => $subject,
            'id' => $subject ? $subject->getId() : null,
            'page_id' => $this->getPage()->getId(),
            'slot_label' => $slot_label,
            'title' => $subject instanceof BlockInterface ? $subject->getName() : 'main page',
            'subject_label' => $subject instanceof BlockInterface ? $this->get('widget.provider')->get($subject->getName())->getLabel() : 'Main page',
        );

        return $this->get('twig')->render($template, $variables);
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

        $defaults = $this->get('widget.provider')->getDefaultVariables($block);

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
            $variableHandler = $this->container->get('variable.provider')->get($variable->getType());
            $config = $this->container->get('widget.provider')->getConfigFor($block->getName(), $variable->getName());

            if ($resolve) {
                // the frontend requires resolved variables (eg: an entity FQDN + id becames the entity itself)
                $vars[$variable->getName()] = $variableHandler->resolve($variable, $config);
            } else {
                // the backend requires only low level 'resolution' (eg: a serialized array get unserialized).
                // since we store all variables in a single field, we need a bit of hacks.
                $vars[$variable->getName()] = $variableHandler->getValue($variable, $config);
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
     * TODO: BUG: let the loader load from all base slots (not only default).
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
            $css []= '/bundles/eightpage/richtexteditor/rte_theme_default.css';

            foreach ($this->container->getParameter('eight_page.admin_css') as $admin_css) {
                $css []= $admin_css;
            }
        }

        return $this->get('twig')->render('@EightPage/Content/util/css.html.twig', array(
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
        }

        $js = $this->appendAssets($js, 'js', $this->container->getParameter('eight_page.js'));

        if ($this->editMode()) {
            $js []= '/bundles/eightpage/js/editor.js';
            $js []= '/bundles/eightpage/richtexteditor/rte.js';
            $js []= '/bundles/eightpage/richtexteditor/plugins/all_plugins.js';

            foreach ($this->container->getParameter('eight_page.admin_js') as $admin_js) {
                $js []= $admin_js;
            }
        }

        $html = $this->get('twig')->render('@EightPage/Content/util/js.html.twig', array(
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