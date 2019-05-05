<?php

namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations;

class HealthCheckController extends FOSRestController
{
    /**
    * @Annotations\Get(
    *     path="/ping"
    * )
    */
    public function getAction()
    {
        return new JsonResponse('pong');
    }
}