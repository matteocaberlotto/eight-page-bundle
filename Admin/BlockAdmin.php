<?php

namespace Eight\PageBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;


class BlockAdmin extends AbstractAdmin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('page')
            ->add('type')
            ->add('layout')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('page')
            ->add('type')
            ->add('layout')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('page')
            ->add('block')
            ->add('type')
            ->add('layout')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                )))
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('append', 'append');
        $collection->add('remove', 'remove');
        $collection->add('update', $this->getRouterIdParameter() . '/update');
        $collection->add('reorder', 'reorder');
        $collection->add('enable', 'enable');
        $collection->add('disable', 'disable');
    }
}