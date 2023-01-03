<?php

namespace App\Controller\Api;

use App\Entity\ActiveDirectoryUser;
use App\Repository\UserRepositoryInterface;
use App\Request\ActiveDirectoryUserRequest;
use App\Response\ErrorResponse;
use App\Response\ListActiveDirectoryUserResponse;
use App\Response\ViolationListResponse;
use App\Security\ActiveDirectoryUserInformation;
use App\Security\UserCreator;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Response\ActiveDirectoryUser as ActiveDirectoryUserResponse;

#[Route(path: '/api/ad_connect')]
#[IsGranted('ROLE_ADCONNECT')]
class ActiveDirectoryConnectController extends AbstractController {

    public function __construct(private UserCreator $userCreator, private UserRepositoryInterface $repository) { }

    /**
     * Returns the list of objectGuids of all Active Directory users.
     *
     * @OA\Get(operationId="api_adconnect_list_users")
     * @OA\Response(
     *     response="200",
     *     description="Returns the list of objectGuids of all Active Directory users.",
     *     @Model(type=ListActiveDirectoryUserResponse::class)
     * )
     */
    #[Route(path: '', methods: ['GET'])]
    public function list(): Response {
        $users = array_map(fn(ActiveDirectoryUser $user) => $this->transformResponse($user), $this->repository->findAllActiveDirectoryUsers());
        return $this->json(new ListActiveDirectoryUserResponse($users));
    }

    /**
     * Adds a new Active Directory user.
     *
     * @OA\Post(operationId="api_adconnect_new_user")
     * @OA\RequestBody(
     *     @Model(type=ActiveDirectoryUserRequest::class)
     * )
     * @OA\Response(
     *     response="201",
     *     description="User was successfully created."
     * )
     * @OA\Response(
     *     response="400",
     *     description="Validation failed.",
     *     @Model(type=ViolationListResponse::class)
     * )
     * @OA\Response(
     *     response="500",
     *     description="Server error.",
     *     @Model(type=ErrorResponse::class)
     * )
     */
    #[Route(path: '', methods: ['POST'])]
    public function add(ActiveDirectoryUserRequest $request): Response {
        $userInfo = $this->transformRequest($request);

        if($this->userCreator->canCreateUser($userInfo)) {
            $user = $this->userCreator->createUser($userInfo);
            $user->setDeletedAt(null); // Adds ability to restore users from Active Directory Connect
            $this->repository->persist($user);

            return new Response(null, Response::HTTP_CREATED);
        }

        return $this->json(
            new ErrorResponse('Cannot create user. Specify a sync rule first.')
        );
    }

    /**
     * Updates an existing Active Directory user.
     *
     * @OA\Patch(operationId="api_adconnect_update_user")
     * @OA\RequestBody(
     *     @Model(type=ActiveDirectoryUserRequest::class)
     * )
     * @OA\Response(
     *     response="200",
     *     description="User was successfully updated."
     * )
     * @OA\Response(
     *     response="400",
     *     description="Validation failed.",
     *     @Model(type=ViolationListResponse::class)
     * )
     * @OA\Response(
     *     response="404",
     *     description="User was not found."
     * )
     * @OA\Response(
     *     response="500",
     *     description="Server error.",
     *     @Model(type=ErrorResponse::class)
     * )
     */
    #[Route(path: '/{objectGuid}', methods: ['PATCH'])]
    public function update(ActiveDirectoryUser $user, ActiveDirectoryUserRequest $request): Response {
        $user = $this->userCreator->createUser($this->transformRequest($request), $user);
        $this->repository->persist($user);
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Removes an Active Directory user.
     *
     * @OA\Delete(operationId="api_adconnect_delete_user")
     *
     * @OA\Response(
     *     response="201",
     *     description="User was successfully removed."
     * )
     * @OA\Response(
     *     response="404",
     *     description="User was not found."
     * )
     * @OA\Response(
     *     response="500",
     *     description="Server error.",
     *     @Model(type=ErrorResponse::class)
     * )
     */
    #[Route(path: '/{objectGuid}', methods: ['DELETE'])]
    public function remove(ActiveDirectoryUser $user): Response {
        $this->repository->remove($user);
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function transformRequest(ActiveDirectoryUserRequest $request): ActiveDirectoryUserInformation {
        return (new ActiveDirectoryUserInformation())
            ->setUsername($request->getSamAccountName())
            ->setUserPrincipalName($request->getUserPrincipalName())
            ->setFirstname($request->getFirstname())
            ->setLastname($request->getLastname())
            ->setEmail($request->getEmail())
            ->setGuid($request->getObjectGuid())
            ->setOu($request->getOu())
            ->setGroups($request->getGroups());
    }

    private function transformResponse(ActiveDirectoryUser $user): ActiveDirectoryUserResponse {
        return new ActiveDirectoryUserResponse($user->getUserIdentifier(), $user->getFirstname(), $user->getLastname(), $user->getGrade(), $user->getObjectGuid());
    }
}