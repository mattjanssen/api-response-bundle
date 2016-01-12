<?php

namespace MattJanssen\ApiResponseBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;
use Symfony\Component\HttpFoundation\Response;

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
class ApiResponse extends ConfigurationAnnotation
{
    /**
     * String used by the Framework Extra Bundle to indicate if this annotation is present on the Request.
     * If this annotation is used, the Request::$attributes bag will include a _api_response item.
     * @see Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener::getConfigurations
     */
    const ALIAS_NAME = 'api_response';

    /**
     * Optional Groups Names for JMS Serializer
     *
     * @var string[]
     */
    private $groups = [];

    /**
     * Override the Default Serializer for this Action
     *
     * One of the Configuration::SERIALIZER_* constants.
     * @see MattJanssen\ApiResponseBundle\DependencyInjection\Configuration::SERIALIZER_JSON_ENCODE
     *
     * @var string
     */
    private $serializer;

    /**
     * HTTP Code for Successful Response
     *
     * @var int
     */
    private $httpCode = Response::HTTP_OK;

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
     * @return \string[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param \string[] $groups
     *
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @return string
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param string $serializer
     *
     * @return $this
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @return int
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
