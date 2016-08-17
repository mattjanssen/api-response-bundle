<?php

namespace MattJanssen\ApiResponseBundle\Test\Authorization;

use MattJanssen\ApiResponseBundle\Authorization\ApiBasicAccessDeniedHandler;
use MattJanssen\ApiResponseBundle\Test\AppMocker;
use MattJanssen\ApiResponseBundle\Test\AppTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ApiBasicAccessDeniedHandlerTest extends AppTestCase
{
    public function testHandle()
    {
        $generatorMock = AppMocker::getApiResponseGenerator();

        $request = new Request();
        $accessDeniedException = new AccessDeniedException();
        $handler = new ApiBasicAccessDeniedHandler($generatorMock);

        $response = $handler->handle($request, $accessDeniedException);

        self::assertJsonStringEqualsArray([
            'data' => null,
            'error' => [
                'code' => Response::HTTP_FORBIDDEN,
                'title' => Response::$statusTexts[Response::HTTP_FORBIDDEN],
                'errorData' => null,
            ],
        ], $response->getContent());

        self::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}
