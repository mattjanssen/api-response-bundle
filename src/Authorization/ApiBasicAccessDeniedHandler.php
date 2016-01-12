<?php

namespace MattJanssen\ApiResponseBundle\Authorization;

use MattJanssen\ApiResponseBundle\Generator\ApiResponseGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

/**
 * API HTTP Basic Access Denied Handler
 *
 * Sends the 403 status code for access denied requests.
 *
 * @see Symfony\Component\Security\Http\EntryPoint\BasicAuthenticationEntryPoint
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiBasicAccessDeniedHandler implements AccessDeniedHandlerInterface
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
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $response = $this->responseGenerator->generateErrorResponse(
            Response::HTTP_FORBIDDEN,
            Response::HTTP_FORBIDDEN,
            Response::$statusTexts[Response::HTTP_FORBIDDEN]
        );

        return $response;
    }
}
