<?php

namespace Eight\PageBundle\Renderer;

use Eight\PageBundle\Form\CollectionMethodType;

class FormBuilder
{
    protected $container, $current_page;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getCurrentPage()
    {
        return $this->container->get('page.renderer')->getPage();
    }

    public function build($block)
    {
        $widget = $this->container->get('widget.provider')->get($block->getName());

        $builder = $this->container->get('form.factory')->createNamedBuilder('form_' . $block->getId());

        $builder
            ->setMethod('post')
            ->setAction($this->container->get('router')->generate('admin_eight_page_block_update', array('id' => $block->getId(), 'page_id' => $this->getCurrentPage()->getId())))
            ;

        foreach ($widget->getVars() as $name => $config) {

            // normalize key only entries
            if (!is_array($config)) {
                $name = $config;
                $config = array(
                    'type' => 'label',
                    );
            }

            if (!isset($config['type'])) {
                $config['type'] = 'label';
            }

            if (isset($config['edit']) && $config['edit'] == false) {
                continue;
            }

            $variable = $this->getDbVariable($block, $config);

            $this->container->get('variable.provider')->get($config['type'])->buildForm($builder, $name, $config, $variable);
        }

        $builder
            ->add('save', 'submit', array(
                'attr' => array(
                    'class' => 'form-control btn-primary submit-block-edit'
                    )
                ))
            ;

        if (!$block->isEnabled()) {
            $builder
                ->add('save-enable', 'submit', array(
                    'label' => 'Save and enable',
                    'attr' => array(
                        'class' => 'form-control btn-success submit-enable-block-edit'
                        )
                    ))
                ;
        }

        $form = $builder->getForm();
        $form->setData($this->container->get('page.renderer')->getDatabaseVariables($block));

        return $form;
    }

    public function getDbVariable($block, $config)
    {
        // ...
        return $this->container->get('eight.content')->findOneBy(array(
            'block' => $block,
            'type' => $config['type'],
            ));
    }
}