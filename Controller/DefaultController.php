<?php

namespace Eight\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class DefaultController extends Controller
{
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

        return $this->redirect($this->generateUrl($this->container->getParameter('eight_page.redirect_home'), array('_locale' => $redirect)));
    }

    /**
     * @Route("/{_locale}", name="homepage", requirements={"_locale": "it|en|de|fr"})
     */
    public function indexAction(Request $request)
    {
        $page = $request->get('content');

        if (!$page->getPublished()) {
            throw new NotFoundHttpException($this->container->getParameter('eight_page.default_not_found_message'));
        }

        $template = $this->get('layout.provider')->provide($page);

        $response = new Response();
        $response->headers->setCookie(new Cookie("selected_language", $request->get('_locale')));

        return $this->render($template, array(), $response);
    }

    public function sitemapAction(Request $request)
    {
        $lastMod = $this->container->getParameter('last_modified');

        $response = new Response;
        $response->setPublic();
        $response->setLastModified(new \DateTime($lastMod));

        if ($response->isNotModified($request)) {
            // return the 304 Response immediately
            return $response;
        }

        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Expires', gmdate("D, d M Y H:i:s", time() + 86400) . " GMT");

        $uris = $this->container->get('bauer.sitemap.url_provider')->provide();

        return $this->render('EightPageBundle:Default:sitemap.xml.twig', array(
            'uris' => $uris
        ), $response);
    }
}
