<?php

namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\RouteResource(
 *     "User",
 *     pluralize=false
 * )
 */
class UserController extends FOSRestController implements ClassResourceInterface
{
    public function postAction(
        Request $request
    ) {
    }
}