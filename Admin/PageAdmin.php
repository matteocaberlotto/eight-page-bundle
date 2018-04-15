<?php

namespace Eight\PageBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use Eight\PageBundle\Form\EventListener\AddRouteFieldSubscriber;
use Eight\PageBundle\Form\EventListener\AddMetaFieldSubscriber;
use Raindrop\RoutingBundle\Entity\Route;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Eight\PageBundle\Entity\Content;
use Eight\PageBundle\Form\DataTransformer\TagsToStringTransformer;


class PageAdmin extends AbstractAdmin implements ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $tagsTransformer = new TagsToStringTransformer('Eight\PageBundle\Entity\Tag', $this->getModelManager());

        $formMapper
            ->add('title')
            ->add('seq', null, array(
                'label' => 'Position',
                'sonata_help' => 'Optional: this field can be used to sort pages.',
                ))
            ->add(
                $formMapper->create('tags', 'text', array(
                    'data' => $this->getSubject()->getTags(),
                    'data_class' => null,
                    'label' => 'TAGS:',
                    'required' => false,
                    'sonata_help' => 'Tagging can be used to retrieve pages',
                ))->addModelTransformer($tagsTransformer)
            )
            ->add('title')
        ;

        $controllerMap = $this->container->get('layout.provider')->provideAll();
        $locales = $this->container->getParameter('eight_page.locales');
        $http_metas = $this->container->getParameter('eight_page.http_metas');

        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber(new AddRouteFieldSubscriber(null, $controllerMap, $locales))
            ->addEventSubscriber(new AddMetaFieldSubscriber($http_metas))
            ;

    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('locale', null, array(
                'template' => 'EightPageBundle:Admin:_list_page_locale.html.twig'
            ))
            ->add('controller', null, array(
                'template' => 'EightPageBundle:Admin:_list_page_controller.html.twig'
            ))
            ->add('tags', null, array(
                'template' => 'EightPageBundle:Admin:_list_page_tags.html.twig'
            ))
            ->add('route', null, array(
                'template' => 'EightPageBundle:Admin:_list_page_route.html.twig'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'layout' => array(
                        'template' => 'EightPageBundle:Admin:list__action_layout.html.twig',
                        )
                )))
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('layout', $this->getRouterIdParameter() . '/layout');
        $collection->add('clone', $this->getRouterIdParameter() . '/clone');
    }

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'EightPageBundle:Admin:page_editor.html.twig';
                break;
            // case 'list':
            //     return 'TeoPageBundle:Admin:page_tree_view.html.twig';
            //     break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('locale', 'doctrine_orm_callback', array(
                'callback' => array($this, 'localeFilterPage')
            ))
            ->add('route.path')
        ;
    }

    public function localeFilterPage($queryBuilder, $alias, $field, $value)
    {
            if(!is_array($value) or !array_key_exists('value', $value)
                or empty($value['value'])){

                return;
            }

            $queryBuilder
                ->leftJoin(sprintf('%s.route', $alias), 'r')
                ->where('r.locale = :locale')
                ->setParameter('locale', $value['value'])
            ;

            return true;
    }

    public function postPersist($page)
    {
        $this->bindRouteToPage($page);
    }

    /**
     * @param type $page
     */
    public function postUpdate($page)
    {
        $this->bindRouteToPage($page);
    }

    protected function bindRouteToPage($page)
    {
        $url = $this->getRequestProperty('url');

        if (!empty($url)) {
            $route = $page->getRoute();
            $controller = !empty($this->getRequestProperty('controller')) ? $this->getRequestProperty('controller') : $this->container->getParameter('eight_page.default_controller');

            if ($route && $route->getPath() == $url) {

                // check if locale has changed
                $locale = $this->getRequestProperty('locale');
                if ($locale !== $route->getLocale()) {
                    $route->setLocale($locale);
                    $this->modelManager->getEntityManager($route)->flush();
                }

                // check if controller has changed
                if ($controller !== $route->getController()) {
                    $route->setController($controller);
                    $this->modelManager->getEntityManager($route)->flush();
                }

                // make sure page is bound to route
                if (!$route->getContent() || $route->getContent() !== $page) {
                    $route->setContent($page);
                    $this->modelManager->getEntityManager($route)->flush();
                }

                return;
            }



            if (!$route) {
                $route = new Route;
                $route->setSchemes(array());
                $route->setResolver($this->container->get('raindrop_routing.content_resolver'));
                $route->setPath($url);
                $route->setNameFromPath();

                $route->setController($controller);
                $route->setLocale($this->getRequestProperty('locale'));
                $this->modelManager->create($route);
            } else {
                $route->setPath($url);
                $route->setNameFromPath();
                $route->setController($controller);
                $route->setLocale($this->getRequestProperty('locale'));
            }

            $page->setRoute($route);

            $route->setContent($page);

            if ($route->getController() != $controller) {
                $route->setController($controller);
            }

            if ($route->getLocale() != $this->getRequestProperty('locale')) {
                $route->setLocale($this->getRequestProperty('locale'));
            }

            $this->modelManager->getEntityManager($route)->flush();
            $this->modelManager->getEntityManager($page)->flush();
        }
    }

    protected function retrieveRoute($url)
    {
        return $this->modelManager
            ->getEntityManager('Raindrop\RoutingBundle\Entity\Route')
            ->getRepository('Raindrop\RoutingBundle\Entity\Route')
            ->findOneByPath($url);
    }

    protected function retrieveTag($name)
    {
        return $this->modelManager
            ->getEntityManager('Eight\PageBundle\Entity\Tag')
            ->getRepository('Eight\PageBundle\Entity\Tag')
            ->findOneByName($name);
    }

    /**
     * Retrieves a form field from sonata post request
     * @param type $name
     * @return null
     */
    protected function getRequestProperty($name)
    {
        $query = $this->request->query->all();
        $uniqid = $query['uniqid'];
        $requestParams = $this->request->request->all();
        $formParams = $requestParams[$uniqid];
        if (isset($formParams[$name])) {
            return $formParams[$name];
        }
        return null;
    }
}