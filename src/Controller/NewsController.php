<?php

namespace App\Controller;

use App\Converter\ModelConverter;
use App\Entity\News;
use App\Form\NewsType;
use App\Manager\NewsManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\RouteResource(
 * "News",
 *     pluralize=false
 *     )
 */
class NewsController extends FOSRestController implements ClassResourceInterface
{
    private $newsManager;

    private $modelConverter;

    public function __construct(NewsManager $newsManager, ModelConverter $modelConverter)
    {
        $this->newsManager = $newsManager;
        $this->modelConverter = $modelConverter;
    }

    /**
     * @Route("/api/news/{id}", name="news.get.one", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function getNewsById(int $id)
    {
        $news = $this->newsManager->getNewsById($id);
        $response = $this->modelConverter->convertModelToArray($news, ['news']);

        return new JsonResponse($response, 200);
    }

    public function postAction(
        Request $request
    ) {
        $data = json_decode(
            $request->getContent(),
            true
        );

        $news = 0;
        $this->newsManager->pushNews($news);

        return new JsonResponse(
            [
                'status' => 'ok',
            ],
            JsonResponse::HTTP_CREATED
        );
    }
}