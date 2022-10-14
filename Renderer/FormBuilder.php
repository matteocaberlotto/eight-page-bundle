<?php

namespace Eight\PageBundle\Renderer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Eight\PageBundle\Form\CollectionMethodType;
use Eight\PageBundle\Variable\Config\Normalizer;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FormBuilder
{
    protected $container, $current_page;

    public function __construct($container, UrlGeneratorInterface $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    public function getCurrentPage()
    {
        return $this->container->get('page.renderer')->getPage();
    }

    /**
     * Builds the form used to edit the widget. All fields are setup according to variable type.
     */
    public function build($block)
    {
        $widget = $this->container->get('widget.provider')->get($block->getName());

        $builder = $this->container->get('form.factory')->createNamedBuilder('form_' . $block->getId());

        $builder
            ->setMethod('post')
            ->setAction($this->router->generate('admin_eight_page_block_update', array(
                'id' => $block->getId(),
                'page_id' => $this->getCurrentPage()->getId()
                ), UrlGeneratorInterface::RELATIVE_PATH))
            ;

        /**
         * Loop through all variables and append its form field.
         */
        foreach ($widget->getVars() as $name => $config) {

            list($name, $config) = Normalizer::normalize($name, $config);

            /**
             * Non editable variables (simple values assigned) are ignored.
             */
            if (!$config->editable()) {
                continue;
            }

            $variable = $this->getDbVariable($block, $name, $config);

            $this->container->get('variable.provider')->get($config->getType())->buildForm($builder, $name, $config, $variable);
        }

        $builder
            ->add('save', SubmitType::class, array(
                'attr' => array(
                    'class' => 'form-control btn-primary submit-block-edit'
                    )
                ))
            ;

        if (!$block->isEnabled()) {
            $builder
                ->add('save-enable', SubmitType::class, array(
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

    public function getDbVariable($block, $name, $config)
    {
        return $this->container->get('eight.content')->findOneBy(array(
            'block' => $block,
            'name' => $name,
            'type' => $config->getType(),
            ));
    }
}