<?php

namespace App\Controller\Api;

use App\Service\IdpExchangeService;
use JMS\Serializer\Exception\Exception;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use SchoolIT\IdpExchange\Request\UpdatedUsersRequest;
use SchoolIT\IdpExchange\Request\UserRequest;
use SchoolIT\IdpExchange\Request\UsersRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/exchange")
 */
class IdpExchangeController extends AbstractApiController {

    private $service;
    private $serializer;
    private $validator;
    private $logger;

    public function __construct(IdpExchangeService $idpExchangeService, SerializerInterface $serializer, ValidatorInterface $validator, LoggerInterface $logger) {
        $this->service = $idpExchangeService;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @Route("/updated_users", name="idp_exchange_updated_users", methods={"POST"})
     */
    public function updatedUsers(Request $request) {
        $json = $request->getContent();
        /** @var UpdatedUsersRequest $exchangeRequest */
        $exchangeRequest = $this->parseAndValidateRequestOrThrowError($json, UpdatedUsersRequest::class);

        $response = $this->service->getUpdatedUsers($exchangeRequest);
        return $this->returnJson($response);
    }

    /**
     * @Route("/users", name="idp_exchange_users", methods={"POST"})
     */
    public function users(Request $request) {
        $json = $request->getContent();
        /** @var UsersRequest $exchangeRequest */
        $exchangeRequest = $this->parseAndValidateRequestOrThrowError($json, UsersRequest::class);

        $response = $this->service->getUsers($exchangeRequest);
        return $this->returnJson($response);
    }

    /**
     * @Route("/user", name="idp_exchange_user", methods={"POST"})
     */
    public function user(Request $request) {
        $json = $request->getContent();
        /** @var UserRequest $exchangeRequest */
        $exchangeRequest = $this->parseAndValidateRequestOrThrowError($json, UserRequest::class);

        $response = $this->service->getUser($exchangeRequest);
        return $this->returnJson($response);
    }

    /**
     * @param string $json
     * @param string $type
     * @return object
     */
    private function parseAndValidateRequestOrThrowError(string $json, string $type) {
        try {
            $request = $this->serializer->deserialize($json, $type, 'json');

            $violations = $this->validator->validate($request);
            if($violations->count() === 0) {
                return $request;
            }

            $this->logFailedValidation($violations);
        } catch (Exception $e) {
            $this->logger->alert(sprintf('Invalid JSON body for type "%s": %s', $type, $e->getMessage()));
        } catch (\Throwable $e) {
            $this->logger->alert(sprintf('Exception "%s" thrown with message "%s"', get_class($e), $e->getMessage()));
        }

        throw new BadRequestHttpException();
    }

    /**
     * @param ConstraintViolationListInterface $violationList
     */
    private function logFailedValidation(ConstraintViolationListInterface $violationList) {
        foreach($violationList as $violation) {
            $this->logger->alert(
                sprintf('Invalid request: property "%s" failed violation with message "%s"', $violation->getPropertyPath(), $violation->getMessage())
            );
        }
    }
}