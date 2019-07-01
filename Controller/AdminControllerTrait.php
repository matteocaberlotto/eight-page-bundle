<?php

namespace Eight\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;

use Eight\PageBundle\Entity\Content;

trait AdminControllerTrait
{
    /**
     * Upate page last updated for caching invalidation
     */
    protected function updatePage($page)
    {
        $page->setUpdatedAtValue();
        $this->container->get('doctrine')->getManager()->flush();
    }

    public function layoutAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository('EightPageBundle:Page')->find($id);

        $page = $entity;
        $page->setEditMode();
        $this->container->get('page.renderer')->setCurrentPage($page);
        $template = $this->container->get('layout.provider')->provide($page);

        return $this->render($template);
    }

    /**
     * @Route("/admin/append/block", name="admin_eight_page_block_append")
     */
    public function appendBlockAction(Request $request)
    {
        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $static = $request->get('is_static') === 'true' ? true : false;

        if (!$static) {
            $subject = $this->get('doctrine')->getRepository($request->get('subject'))->find($request->get('id'));
        } else {
            $subject = $page;
        }

        $page->setEditMode();
        $this->get('page.renderer')->setCurrentPage($page);

        $block = $this->get('helper.page')->append($request->get('subject'), $request->get('id'), $request->get('name'), $request->get('slot_label'), $static);

        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true),
            'form' => $this->get('page.renderer')->appendForm($block, false), // recursive form false
            ));
    }

    /**
     * @Route("/admin/remove/block", name="admin_eight_page_block_remove")
     */
    public function removeBlockAction(Request $request)
    {
        $block = $this->get('eight.blocks')->find($request->get('block_id'));

        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $request->get('block_id')));
        }

        $manager = $this->get('doctrine')->getManager();
        $manager->remove($block);
        $manager->flush();

        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK'
            ));
    }

    /**
     * @Route("/admin/enable/block", name="admin_eight_page_block_enable")
     */
    public function enableAction(Request $request)
    {
        $block = $this->get('eight.blocks')->find($request->get('block_id'));

        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $request->get('block_id')));
        }

        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $page->setEditMode();
        $this->get('page.renderer')->setCurrentPage($page);

        $block->setEnabled(true);
        $manager = $this->get('doctrine')->getManager();
        $manager->flush();

        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true),
            ));
    }

    /**
     * @Route("/admin/disable/block", name="admin_eight_page_block_disable")
     */
    public function disableAction(Request $request)
    {
        $block = $this->get('eight.blocks')->find($request->get('block_id'));

        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $request->get('block_id')));
        }

        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $page->setEditMode();
        $this->get('page.renderer')->setCurrentPage($page);

        $block->setEnabled(false);
        $manager = $this->get('doctrine')->getManager();
        $manager->flush();

        $this->updatePage($page);

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true),
            ));
    }

    /**
     * @Route("/admin/update/block", name="admin_eight_page_block_update")
     */
    public function updateAction(Request $request)
    {
        $object = $this->get('eight.blocks')->find($request->get('id'));
        $page = $this->get('eight.pages')->find($request->get('page_id'));
        $this->get('page.renderer')->setCurrentPage($page);

        $form = $this->get('page.renderer')->createFormForBlock($object);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();

            if ($request->get('enable') == 'true') {
                $object->setEnabled(true);
            }

            foreach ($data as $name => $value) {
                $prev = $object->getContent($name);
                $type = $this->get('widget.provider')->getContentType($object->getName(), $name);

                // if there is no value nor images attached, skip or delete if existing.
                // TODO: find a way to delete images
                if (empty($value)) {
                    if ($prev && ($type != 'image' && $type != 'file')) {
                        $this->get('doctrine')->getManager()->remove($prev);
                    }

                    continue;
                }

                if (!$prev) {
                    $prev = new Content();
                    $prev->setBlock($object);
                    $prev->setName($name);
                    $prev->setType($type);

                    $this->get('doctrine')->getManager()->persist($prev);
                }

                $config = $this->get('widget.provider')->getConfigFor($object->getName(), $name);
                $this->get('variable.provider')->get($prev->getType())->saveValue($prev, $value, $config);
            }

            $this->get('doctrine')->getManager()->flush();

            $this->updatePage($page);
        }

        $page->setEditMode();

        $this->get('doctrine')->getManager()->clear();

        $block = $this->get('eight.blocks')->find($request->get('id'));

        return new JsonResponse(array(
            'status' => 'OK',
            'html' => $this->get('page.renderer')->renderBlock($block, true), // edit mode true
            'form' => $this->get('page.renderer')->appendForm($block, false), // recursive form false
            ));
    }

    /**
     * @Route("/admin/reorder/block", name="admin_eight_page_block_reorder")
     */
    public function reorderAction(Request $request)
    {
        $this->get('helper.page')->reorder($request->get('ids'));

        if ($request->get('page_id')) {
            $page = $this->get('eight.pages')->find($request->get('page_id'));
            $this->updatePage($page);
        }

        return new JsonResponse(array(
            'status' => 'OK'
            ));
    }
}