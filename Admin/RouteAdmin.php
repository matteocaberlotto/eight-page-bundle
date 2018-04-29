<?php

namespace Eight\PageBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;


class RouteAdmin extends AbstractAdmin
{
    protected $container;

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $query
            ->andWhere($query->getRootAlias() . '.controller LIKE :redirect_controller')
            ->setParameter('redirect_controller', 'RaindropRoutingBundle:Generic:redirectRoute');

        return $query;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $em = $this->modelManager->getEntityManager('Raindrop\RoutingBundle\Entity\Route');

        $query = $em->createQueryBuilder('r');

        $query
            ->select('r')
            ->from('Raindrop\RoutingBundle\Entity\Route', 'r')
            ->where('r.controller NOT LIKE :redirect_controller')
            ->orderBy('r.id', 'DESC')
            ->setParameter('redirect_controller', 'RaindropRoutingBundle:Generic:redirectRoute');
            ;

        $formMapper
            ->add('path')
            ->add('content', 'sonata_type_model', array(
                // 'btn_add' => true,
                // 'mapped' => false,
                'class' => 'Raindrop\RoutingBundle\Entity\Route',
                'query' => $query,
                ))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('path')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('path')
            ->add('routeContent', null, array(
                'label' => 'Redirects to...',
                'template' => 'EightPageBundle:Admin:_route_content.html.twig',
                ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                )))
        ;
    }

    public function preUpdate($object)
    {
        $this->setupRoute($object);
    }

    public function prePersist($object)
    {
        $this->setupRoute($object);
    }

    protected function setupRoute($route)
    {
        if (empty($route->getName())) {
            $route->setNameFromPath();
        }

        if (empty($route->getController())) {
            $route->setController('RaindropRoutingBundle:Generic:redirectRoute');
        }
    }
}
