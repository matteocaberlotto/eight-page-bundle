<?php

namespace Eight\PageBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eight\PageBundle\Model\PageInterface;

class Extension extends \Twig_Extension implements ContainerAwareInterface
{
    protected $seopage;

    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function __construct($seopage)
    {
        $this->seopage = $seopage;
    }

    public function getGlobals()
    {
        $defaults = array(
            "locale" => $this->getLocale(),
            "page" => $this->getPage(),
            "locales" => $this->container->getParameter('eight_page.locales'),
        );

        return $defaults;
    }

    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'i18n_path'      => new \Twig_Function_Method($this, 'i18nPath'),
            'i18n_title'      => new \Twig_Function_Method($this, 'i18nTitle'),
            'route_name'        => new \Twig_Function_Method($this, 'routeName'),
            'render_title'      => new \Twig_Function_Method($this, 'renderTitle'),
            'render_encoding'      => new \Twig_Function_Method($this, 'renderEncoding'),
            'render_metadatas'  => new \Twig_Function_Method($this, 'renderMetadatas'),
            'is_current_path' => new \Twig_Function_Method($this, 'isCurrentPath'),
            'render_page_content' => new \Twig_Function_Method($this, 'renderPage', array('is_safe' => array('html'))),
            'render_inner_blocks' => new \Twig_Function_Method($this, 'renderBlocks', array('is_safe' => array('html'))),
            'is_page' => new \Twig_Function_Method($this, 'isPage'),
            'eight_stylesheets' => new \Twig_Function_Method($this, 'stylesheets', array('is_safe' => array('html'))),
            'eight_javascripts' => new \Twig_Function_Method($this, 'javascripts', array('is_safe' => array('html'))),
            'eight_body_class' => new \Twig_Function_Method($this, 'bodyClass'),
            'is_host' => new \Twig_Function_Method($this, 'isHost'),
            'is_route' => new \Twig_Function_Method($this, 'isRoute'),
            'if_route' => new \Twig_Function_Method($this, 'showWhenRouteMatch'),
            'get_widget' => new \Twig_Function_Method($this, 'getWidget'),
            'get_page' => new \Twig_Function_Method($this, 'findPage'),
            'get_breadcrumbs' => new \Twig_Function_Method($this, 'getBreadcrumbs'),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('json_encode', 'json_encode'),
            );
    }

    public function getBreadcrumbs()
    {
        $candidates = array();

        $path = $this->getCurrentPath();

        $fragments = explode(DIRECTORY_SEPARATOR, $path);

        $url = array();
        foreach ($fragments as $url_part) {
            $url []= $url_part;
            $parent_url = implode(DIRECTORY_SEPARATOR, $url);

            $route = $this->container->get('raindrop_routing.route_repository')->findOneBy(array(
                'path' => $parent_url
                ));

            if ($route && $route->getContent()) {
                $candidates []= $route->getContent();
            }
        }

        return $candidates;
    }

    public function getCurrentPath()
    {
        $page = $this->getPage();

        if ($page) {
            return $page->getRoute()->getPath();
        }

        return $this->container->get('router')->generate($this->getRequest()->get('_route'));
    }

    public function showWhenRouteMatch($routes, $string)
    {
        if (is_array($routes)) {
            foreach ($routes as $route) {
                if ($this->findPage($route) == $this->getPage()) {
                    return $string;
                }
            }
        } else {
            if ($this->findPage($routes) == $this->getPage()) {
                return $string;
            }
        }

        return '';
    }

    public function getWidget($name)
    {
        return $this->container->get('widget.provider')->get($name);
    }

    public function isRoute($path) {
        return ($this->findPage($path) == $this->getPage());
    }

    public function isHost($test)
    {
        $host = $this->getRequest()->getSchemeAndHttpHost();
        return strpos($host, $test) !== false;
    }

    public function bodyClass()
    {
        if ($this->getPage()) {
            return $this->getPage()->getRoute()->getName();
        }

        return '';
    }

    public function stylesheets()
    {
        return $this->container->get('page.renderer')->css();
    }

    public function javascripts()
    {
        return $this->container->get('page.renderer')->js();
    }

    public function isPage($subject)
    {
        return $subject instanceof PageInterface;
    }

    public function renderBlocks($subject, $type = 'default')
    {
        return $this->container->get('page.renderer')->renderBlockChildren($subject, $type);
    }

    public function renderPage($type = 'default')
    {
        $page = $this->getPage();

        return $this->container->get('page.renderer')->renderBlockChildren($page, $type);
    }

    public function getLocale()
    {
        $locale = $this->getRequest()->get('_locale');

        if (empty($locale)) {
            return $this->container->getParameter('locale');
        }

        return $locale;
    }

    public function isCurrentPath($label)
    {
        $currentPage = $this->getPage();
        $candidatePage = $this->container->get('eight.pages')->findOneByTag($label . '.' . $this->getLocale());

        if ($currentPage && $candidatePage && $currentPage->getId() == $candidatePage->getId()) {
            return 'active';
        }

        return '';
    }

    /**
     * This will retrieve a page route using several methods:
     * - if the argument passed is the page, return the route directly
     * - search for a page tagged "<name>.<locale>"
     * - search for a page tagged "<name>"
     * - search for a route named "<name>"
     */
    public function findPage($name)
    {
        // if it is a page, simply return it
        if ($name instanceof PageInterface) {
            return $name;
        }

        // search for localized tag first (eg: "homepage.it")
        $page = $this->container->get('eight.pages')->findOneByTag($name . '.' . $this->getLocale());

        if ($page && is_object($page->getRoute())) {
            return $page;
        }

        // search for the tag
        $page = $this->container->get('eight.pages')->findOneByTag($name);

        if ($page && is_object($page->getRoute())) {
            return $page;
        }

        // search for route name
        $route = $this->container->get('raindrop_routing.route_repository')->findOneBy(array(
            'name' => $name,
            ));

        if ($route && $route->getContent() instanceof PageInterface) {
            return $route->getContent();
        }

        return false;
    }

    public function i18nPath($path, $params = array())
    {
        $page = $this->findPage($path);

        if ($page) {
            return $this->container->get('router')->generate($page->getRoute()->getName(), $params);
        }
    }

    public function i18nTitle($path)
    {
        $page = $this->findPage($path);

        if ($page) {
            return $page->getTitle();
        }
    }

    public function routeName($tag)
    {

        $page = $this->container->get('eight.pages')->findOneByTag($tag);
        if ($page && $page->getRoute()) {
            return $page->getRoute()->getName();
        }
    }

    public function getPage()
    {
        return $this->getRequest()->get('content');
    }

    /**
     * @param $string
     * @return mixed
     */
    private function normalize($string)
    {
        return htmlentities(strip_tags($string), ENT_COMPAT, $this->seopage->getEncoding());
    }

    /**
     * @return void
     */
    public function renderEncoding()
    {
        echo sprintf('<meta charset="%s">', strip_tags($this->seopage->getEncoding()));
    }

    /**
     * @return void
     */
    public function renderTitle()
    {
        echo strip_tags($this->seopage->getTitle());
    }

    /**
     * @return void
     */
    public function renderMetadatas()
    {
        $return = '';

        foreach ($this->seopage->getMetas() as $type => $metas) {
            foreach ((array) $metas as $name => $meta) {
                list($content, $extras) = $meta;

                $return .= sprintf("<meta %s=\"%s\" content=\"%s\" />\n",
                    $type,
                    $this->normalize($name),
                    $this->normalize($content)
                );

            }
        }

        echo $return;
    }

    public function getName()
    {
        return 'eight_page';
    }
}