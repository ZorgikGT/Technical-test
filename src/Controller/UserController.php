<?php

namespace App\Controller;


use App\Converter\ModelConverter;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\UserBundle\Model\UserManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * @Rest\RouteResource(
 *     "User",
 *     pluralize=false
 * )
 */
class UserController extends AbstractFOSRestController implements ClassResourceInterface
{
    /**
     * @var UserManagerInterface $userManager
     */
    private $userManager;

    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    /**
     * @var ModelConverter $modelConverter
     */
    private $modelConverter;

    /**
     * @var EntityManagerInterface $emi
     */
    private $emi;

    /**
     * @var EncoderFactoryInterface $passwordUpdater
     */
    private $passwordUpdater;

    /**
     * @var PaginatorInterface $paginator
     */
    private $paginator;

    /**
     * UserController constructor.
     * @param UserManagerInterface $userManager
     * @param ValidatorInterface $validator
     * @param ModelConverter $modelConverter
     * @param EntityManagerInterface $emi
     * @param EncoderFactoryInterface $passwordUpdater
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        UserManagerInterface $userManager,
        ValidatorInterface $validator,
        ModelConverter $modelConverter,
        EntityManagerInterface $emi,
        EncoderFactoryInterface $passwordUpdater,
        PaginatorInterface $paginator
    )
    {
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->modelConverter = $modelConverter;
        $this->emi = $emi;
        $this->passwordUpdater = $passwordUpdater;
        $this->paginator = $paginator;
    }


    /**
     * Create user.
     *
     * Create user by 3 fields: email, username, password and repeat password.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the message and status code."
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     type="string",
     *     description="The field used to set email."
     * )
     * @SWG\Parameter(
     *     name="username",
     *     in="query",
     *     type="string",
     *     description="The field used to set username."
     * )
     * @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     type="string",
     *     description="The field used to set password."
     * )
     * @SWG\Parameter(
     *     name="repeat password",
     *     in="query",
     *     type="string",
     *     description="The field used to make sure password is right."
     * )
     * @SWG\Tag(name="user")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function postAction(
        Request $request
    ) {
        $json = $request->getContent();
        $data = json_decode($json, true);

        try {
            $user = $this->userManager->findUserByUsername($data['username']);
        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        if(!empty($user)) {

            return new JsonResponse(['message' => 'username already exist'],JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userManager->findUserByEmail($data['email']);
        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        if(!empty($user)) {

            return new JsonResponse(['message' => 'email already exist'],JsonResponse::HTTP_BAD_REQUEST);
        }

        /**
         * @var \FOS\UserBundle\Model\User $user
         */
        $user = $this->modelConverter->convertJsonToModel($json, User::class);

        if($user->getPassword() !== $user->getPlainPassword()) {

            return new JsonResponse(['message' => 'passwords are different'],JsonResponse::HTTP_BAD_REQUEST);
        }
        $encoder = $this->passwordUpdater->getEncoder($user);
        $user->setSalt(User::SALT);
        $user->setPassword(
            $encoder->encodePassword(
                $user->getPassword(),
                $user->getSalt()
            )
        );
        $user->setEnabled(true);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {

            $errorsString = (string) $errors;

            return new JsonResponse(['message' => $errorsString],JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->emi->persist($user);
        $this->emi->flush();
        $this->emi->clear();

        return new JsonResponse(['status' => 'ok'], JsonResponse::HTTP_CREATED);
    }

    /**
     * Get user.
     *
     * Returns user by unique identifier.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the user and status code."
     * )
     * @SWG\Parameter(
     *    name="id",
     *     in="query",
     *     type="integer",
     *     description="The field used to get user."
     * )
     *
     * @SWG\Tag(name="user")
     *
     * @param int $id
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getAction(int $id)
    {
        try {
            $user = $this->userManager->findUserBy(['id' => $id]);
        }
        catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->modelConverter->convertModelToArray($user, ['user']);
        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['user' => $user], JsonResponse::HTTP_CREATED);
    }

    /**
     * Return paginate list of users.
     *
     * Return paginate list of users with fields: id, title, last login.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Render paginate list of users."
     * )
     * @SWG\Parameter(
     *     name="page number",
     *     in="query",
     *     type="integer",
     *     description="The field used to get page"
     * )
     * @SWG\Tag(name="user")
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cgetAction()
    {
        $pageNumber = 1;

        try {
            $users = $this->userManager->findUsers();

            if(!empty($_GET['page'])) {
                $pageNumber = $_GET['page'];
            }
        }catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        $pagination = $this->paginator->paginate(
            $users,
            $pageNumber,
            5
        );

        return $this->render( 'paginate/user.html.twig', array('pagination' => $pagination));
    }

    /**
     * Update user
     *
     * Update user. Return the status code
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the message and status code."
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="integer",
     *     description="The field used to get user."
     * )
     * @SWG\Parameter(
     *     name="username",
     *     in="query",
     *     type="string",
     *     description="The field used to update username."
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     type="string",
     *     description="The field used to update email."
     * )
     * @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     type="string",
     *     description="The field used to update password."
     * )
     * @SWG\Tag(name="user")
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
            $user = $this->userManager->findUserBy(['id' => $id]);
        }
        catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $testUser = $this->userManager->findUserByUsername($data['username']);

            if(!is_null($testUser) && $user->getId() !== $testUser->getId()) {

                return new JsonResponse(['message' => 'Username already exist'], JsonResponse::HTTP_BAD_REQUEST);
            }

        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $testUser = $this->userManager->findUserByEmail($data['email']);

            if(!is_null($testUser) && $user->getId() !== $testUser->getId()) {

                return new JsonResponse(['message' => 'Email already exist'],JsonResponse::HTTP_BAD_REQUEST);
            }


        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        if(isset($data['username'])) {
            $user->setUsername($data['username']);
        }

        if(isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if(isset($data['password'])) {
            $user->setPassword($data['password']);
            $encoder = $this->passwordUpdater->getEncoder($user);
            $user->setSalt(User::SALT);
            $user->setPassword(
                $encoder->encodePassword(
                    $user->getPassword(),
                    $user->getSalt()
                )
            );
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {

            $errorsString = (string) $errors;

            return new JsonResponse(['message' => $errorsString],JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->emi->persist($user);
        $this->emi->flush();
        $this->emi->clear();

        return new JsonResponse(['message' => 'updated'], JsonResponse::HTTP_OK);
    }

    /**
     * Delete the user.
     *
     * Delete the user. Return the status code.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the message and status code."
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="integer",
     *     description="The field used to delete user."
     * )
     *
     * @SWG\Tag(name="user")
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAction(int $id)
    {
        try {
            $user = $this->userManager->findUserBy(['id' => $id]);
            $this->userManager->deleteUser($user);
        } catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'deleted'], JsonResponse::HTTP_OK);
    }
}
