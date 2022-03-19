<?php

namespace Eight\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function __construct(
        $layout_provider,
        $sitemap_provider,
        $home_redirect,
        $not_found_message)
    {
        $this->layout_provider = $layout_provider;
        $this->sitemap_provider = $sitemap_provider;
        $this->home_redirect = $home_redirect;
        $this->not_found_message = $not_found_message;
    }

    /**
     * @Route("/", name="home_redirect")
     */
    public function redirectHomeAction(Request $request)
    {
        $redirect = $request->cookies->get('selected_language');

        $langs = array('it', 'en', 'de', 'fr');
        if (empty($redirect) or !isset($langs[$redirect])) {
            $requestLanguage = $request->getPreferredLanguage();

            foreach ($langs as $lang) {
                if (strpos(strtolower($requestLanguage), $lang) !== false) {
                    $redirect = $lang;
                    break;
                }
            }
        }

        if (empty($redirect)) {
            $redirect = $this->container->getParameter('locale');
        }

        return $this->redirect($this->generateUrl($this->home_redirect, array('_locale' => $redirect)));
    }

    /**
     * @Route("/{_locale}", name="eight_homepage", requirements={"_locale": "it|en|de|fr"})
     */
    public function indexAction(Request $request)
    {
        $page = $request->get('content');

        if (!$page || !$page->getPublished()) {
            throw new NotFoundHttpException($this->not_found_message);
        }

        $template = $this->layout_provider->provide($page);

        $response = new Response();
        $response->headers->setCookie(new Cookie("selected_language", $request->get('_locale')));

        return $this->render($template, [
            'page' => $page,
            ], $response);
    }

    public function sitemapAction(Request $request)
    {
        $response = new Response;
        $response->setPublic();
        $response->setLastModified(new \DateTime());

        if ($response->isNotModified($request)) {
            // return the 304 Response immediately
            return $response;
        }

        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Expires', gmdate("D, d M Y H:i:s", time() + 86400) . " GMT");

        $uris = $this->sitemap_provider->provide();

        return $this->render('EightPageBundle:Default:sitemap.xml.twig', array(
            'uris' => $uris
        ), $response);
    }
}
