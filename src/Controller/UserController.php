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

    public function cgetAction()
    {
        try {
            $users = $this->userManager->findUsers();
        }catch (\Exception $e) {

            return new JsonResponse(['message' => $e->getMessage()],JsonResponse::HTTP_BAD_REQUEST);
        }

        $pagination = $this->paginator->paginate(
            $users,
            1,
            5
        );

        return $this->render( 'paginate/user.html.twig', array('pagination' => $pagination));
    }

    /**
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

    public function putAction(Request $request, int $id)
    {
        $json = $request->getContent();
        $data = json_decode($json, true);
    }
}
