<?php

namespace App\Controller;

use App\Converter\ModelConverter;
use App\Entity\News;
use App\Manager\NewsManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\UserBundle\Model\UserManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Rest\RouteResource(
 * "News",
 *     pluralize=false
 *     )
 */
class NewsController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var NewsManager $newsManager
     */
    private $newsManager;

    /**
     * @var ModelConverter $modelConverter
     */
    private $modelConverter;

    /**
     * @var UserManagerInterface $userManager
     */
    private $userManager;

    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    /**
     * @var PaginatorInterface $paginator
     */
    private $paginator;

    /**
     * NewsController constructor.
     * @param NewsManager $newsManager
     * @param ModelConverter $modelConverter
     * @param UserManagerInterface $userManager
     * @param ValidatorInterface $validator
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        NewsManager $newsManager,
        ModelConverter $modelConverter,
        UserManagerInterface $userManager,
        ValidatorInterface $validator,
        PaginatorInterface $paginator
    )
    {
        $this->newsManager = $newsManager;
        $this->modelConverter = $modelConverter;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->paginator = $paginator;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $json = $request->getContent();
        $data = json_decode($json, true);

        /**
         * @var News $news
         */
        $news = $this->modelConverter->convertJsonToModel($json, News::class, ['createdBy']);

        $user = $this->userManager->findUserByUsername($data['createdBy']);
        $news->setCreatedBy($user);

        $errors = $this->validator->validate($news);

        if (count($errors) > 0) {

            $errorsString = (string) $errors;

            return new JsonResponse(['message' => $errorsString],JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->newsManager->addNews($news);

        return new JsonResponse(['status' => 'ok'], JsonResponse::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getAction(int $id)
    {
        try {
            /**
             * @var News $news
             */
            $news = $this->newsManager->getNewsById($id);
            $user = $this->userManager->findUserBy(['id' => $news->getCreatedBy()->getId()]);
        }
        catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $news = $this->modelConverter->convertModelToArray($news, ['news']);
            $news['createdBy'] = $user->getUserName();
        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['news' => $news], JsonResponse::HTTP_CREATED);
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        try {
            $news = $this->newsManager->getAllNews();
        }catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        $pagination = $this->paginator->paginate(
            $news,
            1,
            5
        );

        return $this->render( 'paginate/news.html.twig', array('pagination' => $pagination));
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAction(int $id)
    {
        try {
            $news = $this->newsManager->getNewsById($id);
            $this->newsManager->deleteNews($news);
        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'deleted'], JsonResponse::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function putAction(Request $request, int $id)
    {
        $json = $request->getContent();
        $data = json_decode($json, true);

        try {
            /**
             * @var News $news
             */
            $news = $this->newsManager->getNewsById($id);
        }
        catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        if(isset($data['title'])) {
            $news->setTitle($data['title']);
        }

        if(isset($data['description'])) {
            $news->setDescription($data['description']);
        }

        $errors = $this->validator->validate($news);
        if (count($errors) > 0) {

            $errorsString = (string) $errors;

            return new JsonResponse(['message' => $errorsString],JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->newsManager->addNews($news);

        return new JsonResponse(['message' => 'updated'], JsonResponse::HTTP_OK);
    }
}
