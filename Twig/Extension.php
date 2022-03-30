<?php

namespace Eight\PageBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Eight\PageBundle\Seo\SeoPage;
use Eight\PageBundle\Model\PageInterface;

class Extension extends AbstractExtension
{
    protected $seopage;

    protected $counters = array();

    protected $container;

    protected $sonata_admin_enabled = false;
    protected $easy_admin_enabled = false;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function __construct(SeoPage $seopage, UrlGeneratorInterface $router)
    {
        $this->seopage = $seopage;
        $this->router = $router;
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
        $twig = $this;

        return array(
            new TwigFunction('i18n_path', array($this, 'i18nPath')),
            new TwigFunction('i18n_title', array($this, 'i18nTitle')),
            new TwigFunction('route_name', array($this, 'routeName')),
            new TwigFunction('render_title', array($this, 'renderTitle')),
            new TwigFunction('render_encoding', array($this, 'renderEncoding')),
            new TwigFunction('render_metadatas', array($this, 'renderMetadatas')),
            new TwigFunction('is_current_path', array($this, 'isCurrentPath')),
            new TwigFunction('render_page_content', array($this, 'renderPage'), array('is_safe' => array('html'))),
            new TwigFunction('render_inner_blocks', array($this, 'renderBlocks'), array('is_safe' => array('html'))),
            new TwigFunction('render_static_blocks', array($this, 'renderStaticBlocks'), array('is_safe' => array('html'))),
            new TwigFunction('is_page', array($this, 'isPage')),
            new TwigFunction('eight_stylesheets', array($this, 'stylesheets'), array('is_safe' => array('html'))),
            new TwigFunction('eight_javascripts', array($this, 'javascripts'), array('is_safe' => array('html'))),
            new TwigFunction('eight_body_class', array($this, 'bodyClass')),
            new TwigFunction('is_host', array($this, 'isHost')),
            new TwigFunction('is_route', array($this, 'isRoute')),
            new TwigFunction('if_route', array($this, 'showWhenRouteMatch'), array('is_safe' => array('html'))),
            new TwigFunction('get_widget', array($this, 'getWidget')),
            new TwigFunction('get_page', array($this, 'findPage')),
            new TwigFunction('get_breadcrumbs', array($this, 'getBreadcrumbs')),
            new TwigFunction('current_index', array($this, 'currentIndex')),
            new TwigFunction('eight_page_sonata_admin_enabled', function () use ($twig) {
                return $twig->sonata_admin_enabled;
            }),
            new TwigFunction('eight_page_easy_admin_enabled', function () use ($twig) {
                return $twig->easy_admin_enabled;
            }),
        );
    }

    public function setSonataAdminEnabled()
    {
        $this->sonata_admin_enabled = true;
    }

    public function setEasyAdminEnabled()
    {
        $this->easy_admin_enabled = true;
    }

    public function currentIndex($label, $increment = true)
    {
        if (!isset($this->counters[$label])) {
            $this->counters[$label] = 0;
        }

        if ($increment) {
            $this->counters[$label]++;
        }

        return $this->counters[$label];
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('json_encode', 'json_encode'),
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

        return $this->router->generate($this->getRequest()->get('_route'), $this->getRequest()->get('_route_params'));
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
        if (!$this->getPage()) {
            $route = $this->getRequest()->get('_route');

            if (is_array($path)) {
                foreach ($path as $p) {
                    if ($this->findPage($p) && $this->findPage($p)->getRoute()->getName() == $route) {
                        return true;
                    }
                }

                return false;
            } else {
                return ($this->findPage($path) && $this->findPage($path)->getRoute()->getPath() == $route);
            }
        }

        if (is_array($path)) {
            foreach ($path as $p) {
                if ($this->findPage($p) == $this->getPage()) {
                    return true;
                }
            }

            return false;
        } else {
            return ($this->findPage($path) == $this->getPage());
        }

    }

    public function isHost($test)
    {
        $host = $this->getRequest()->getSchemeAndHttpHost();
        return strpos($host, $test) !== false;
    }

    public function bodyClass()
    {
        if ($this->getPage() && $this->getPage()->getRoute()) {
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

    public function renderStaticBlocks($type = 'default')
    {
        return $this->container->get('page.renderer')->renderStaticBlocks($type);
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
        $page = $this->getPage();

        if ($page && $page->getLocale()) {
            return $page->getLocale();
        }

        $request = $this->getRequest();

        if ($request) {
            $locale = $request->get('_locale');

            if (empty($locale)) {
                return $this->container->getParameter('locale');
            }

            return $locale;
        }
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
            if ($this->editMode()) {
                if ($this->sonata_admin_enabled) {
                    return $this->router->generate($this->container->getParameter('eight_page.edit_page_url'), ['id' => $page->getId()]);
                }
                
                if ($this->easy_admin_enabled) {
                    return $this->router->generate($this->container->getParameter('eight_page.easyadmin_base_path'), [
                        'entity' => 'Pages',
                        'action' => 'layout',
                        'id' => $page->getId()
                    ]);
                }
            } else {
                return $this->router->generate($page->getRoute()->getName(), $params);
            }
        }
    }

    public function editMode()
    {
        return $this->container->get('page.renderer')->editMode();
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
        $request = $this->getRequest();

        if ($request) {
            return $request->get('content');
        }
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