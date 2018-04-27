<?php

namespace Eight\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Yaml\Yaml;

class PageCRUDController extends CRUDController
{
    public function layoutAction(Request $request)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $page = $object;
        $page->setEditMode();
        $this->get('page.renderer')->setCurrentPage($page);
        $template = $this->get('layout.provider')->provide($page);

        return $this->render($template);
    }

    public function cloneAction(Request $request)
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $clone = $this->get('helper.page')->duplicatePage($object);

        return $this->redirectTo($clone);
    }

    public function exportYmlAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $response = new Response();

        // Set headers
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Type', 'text/html; charset=utf-8');

        if (!$request->get('view')) {
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $object->getTitle() . '.yml'
            ));
        }

        $response->setContent(Yaml::dump(array('pages' => array(
            $object->toArray()
            )), 64));

        return $response;
    }
}