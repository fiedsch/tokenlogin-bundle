<?php // -*- coding: utf-8 -*-

namespace Fiedsch\TokenloginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


/**
 * Handles the Template Bunldle's frontend routes.
 *
 * @Route(defaults={"_scope" = "frontend", "_token_check" = true})
 */
class TokenloginController extends Controller
{
    /**
     * Checks token and logs user in
     *
     * @return Response
     *
     * @Route("/tokenlogin/{token}", name="tokenlogin")
     */
    public function demoAction($token)
    {

        return new Response("i was called with ${token}");

        // $controller = new Fiedsch\TemplateBundle\Controller\SomeController();
        // return $controller->run();

    }

}
