<?php

namespace MattJanssen\ApiResponseBundle\EntryPoint;

use MattJanssen\ApiResponseBundle\Generator\ApiResponseGenerator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * API HTTP Basic Authentication Start
 *
 * Sends the 401 status code for unauthenticated requests, but without the WWW-Authenticate header.
 *
 * @see Symfony\Component\Security\Http\EntryPoint\BasicAuthenticationEntryPoint
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiBasicAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * API Response Generator
     *
     * @var ApiResponseGenerator
     */
    private $responseGenerator;

    /**
     * Constructor
     *
     * @param ApiResponseGenerator $responseGenerator
     */
    public function __construct(
        ApiResponseGenerator $responseGenerator
    ) {
        $this->responseGenerator = $responseGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = $this->responseGenerator->generateErrorResponse(
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_UNAUTHORIZED,
            Response::$statusTexts[Response::HTTP_UNAUTHORIZED]
        );

        $response->headers->set('WWW-Authenticate', 'Basic realm="test"');

        return $response;
    }
}
