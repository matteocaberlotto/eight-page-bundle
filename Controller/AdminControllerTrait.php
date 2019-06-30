<?php

namespace Eight\PageBundle\Controller;

trait AdminControllerTrait
{
    public function layoutAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository(User::class)->find($id);

        $page = $object;
        $page->setEditMode();
        $this->container->get('page.renderer')->setCurrentPage($page);
        $template = $this->container->get('layout.provider')->provide($page);

        return $this->render($template);
    }
}