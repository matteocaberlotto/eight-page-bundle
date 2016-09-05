<?php

namespace Eight\PageBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AddRouteFieldSubscriber implements EventSubscriberInterface
{
    private $options, $controllerMap, $locales;

    public function __construct($options = array('url' => '', 'controller' => ''), $controllerMap = array(), $locales = array())
    {
        $this->options = $options;
        $this->controllerMap = $controllerMap;
        $this->locales = $locales;
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        // During form creation setData() is called with null as an argument
        // by the FormBuilder constructor. You're only concerned with when
        // setData is called with an actual Entity object in it (whether new
        // or fetched with Doctrine). This if statement lets you skip right
        // over the null condition.
        if (null === $data) {
            return;
        }

        $url = $this->options['url'];
        $controller = $this->options['controller'];
        $locale = '';
        $name = '';

        if ($data->getRoute()) {
            // obj is not new so retrieve route
            $route = $data->getRoute();
            if ($route) {
                $url = $route->getPath();
                $controller = $route->getController();
                $locale = $route->getLocale();
                $name = $route->getName();
            }
        }

        $form->add('url', 'text', array(
            'mapped' => false,
            'data' => $url,
            'sonata_help' => 'Route label: ' . $name,
            'attr' => array(
                'class' => 'page-url'
            ),
        ));

        $form->add('controller', null, array(
            'mapped' => false,
            'data' => $controller,
            'sonata_help' => 'Optional custom controller, leave blank for default.',
        ));

        $form->add('locale', 'choice', array(
            'mapped' => false,
            'data' => $locale,
            'choices' => $this->locales
        ));
    }

    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }
}
