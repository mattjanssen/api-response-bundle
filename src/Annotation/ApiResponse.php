<?php

namespace MattJanssen\ApiResponseBundle\Annotation;

use MattJanssen\ApiResponseBundle\Model\ApiConfigInterface;
use MattJanssen\ApiResponseBundle\Model\ApiConfigTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Annotation for Returning an API Response
 *
 * To produce a successful response, return an array or object that will be serialized
 * into the 'data' field of the API response.
 * To produce an error response, throw an ApiResponseException from the controller action.
 *
 * @Annotation
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponse extends ConfigurationAnnotation implements ApiConfigInterface
{
    use ApiConfigTrait;

    /**
     * String used by the Framework Extra Bundle to indicate if this annotation is present on the Request.
     * If this annotation is used, the Request::$attributes bag will include a _api_response item.
     * @see Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener::getConfigurations
     */
    const ALIAS_NAME = 'api_response';

    /**
     * HTTP Code for Successful Response
     *
     * @var int
     */
    private $httpCode;

    /**
     * {@inheritdoc}
     */
    public function getAliasName()
    {
        return 'api_response';
    }

    /**
     * {@inheritdoc}
     */
    public function allowArray()
    {
        return false;
    }

    /**
     * @return int|null
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param int $httpCode
     *
     * @return $this
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;

        return $this;
    }
}
