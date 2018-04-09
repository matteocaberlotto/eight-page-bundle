<?php

namespace Eight\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sonata\AdminBundle\Controller\CRUDController;

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
}