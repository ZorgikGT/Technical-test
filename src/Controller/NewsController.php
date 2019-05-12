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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

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
     * Create news.
     *
     * Create news by 3 fields: title, description, createdBy(author username)
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the message and status code."
     * )
     * @SWG\Parameter(
     *     name="title",
     *     in="query",
     *     type="string",
     *     description="The field used to set title."
     * )
     * @SWG\Parameter(
     *     name="description",
     *     in="query",
     *     type="string",
     *     description="The field used to set description."
     * )
     * @SWG\Parameter(
     *     name="createdBy",
     *     in="query",
     *     type="string",
     *     description="The field used to set author."
     * )
     * @SWG\Tag(name="news")
     *
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
     * Get news by id.
     *
     * Return news by unique identifier.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the news."
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="integer",
     *     description="The field used to get news."
     * )
     * @SWG\Tag(name="news")
     *
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
     * Return paginate list of news.
     *
     * Return paginate list of news with fields: id, title, release date.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Render paginate list of news."
     * )
     * @SWG\Parameter(
     *     name="page number",
     *     in="query",
     *     type="integer",
     *     description="The field used to get page"
     * )
     * @SWG\Tag(name="news")
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        $pageNumber = 1;
        try {
            $news = $this->newsManager->getAllNews();

            if(!empty($_GET['page'])) {
                $pageNumber = $_GET['page'];
            }
        }catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        $pagination = $this->paginator->paginate(
            $news,
            $pageNumber,
            5
        );

        return $this->render( 'paginate/news.html.twig', array('pagination' => $pagination));
    }

    /**
     * Update of news
     *
     * Update of news. Return the status code
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the message and status code."
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="integer",
     *     description="The field used to get news."
     * )
     * @SWG\Parameter(
     *     name="title",
     *     in="query",
     *     type="string",
     *     description="The field used to update title."
     * )
     * @SWG\Parameter(
     *     name="description",
     *     in="query",
     *     type="string",
     *     description="The field used to update description."
     * )
     * @SWG\Tag(name="news")
     *
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

    /**
     * Delete the news.
     *
     * Delete the news. Return the status code.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the message and status code."
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="integer",
     *     description="The field used to delete news."
     * )
     *
     * @SWG\Tag(name="news")
     *
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
}
