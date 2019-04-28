<?php

namespace App\Controller;

use App\Manager\NewsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    private $newsManager;

    public function __construct(NewsManager $newsManager)
    {
        $this->newsManager = $newsManager;
    }

    /**
     * @Route("/api/news/{id}", name="news.get.one", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function getNewsById(int $id)
    {
        $news = $this->newsManager->getNewsById($id);
        var_dump($news);
//        $news = json_encode($news);

        return new JsonResponse("", 200);
    }

}