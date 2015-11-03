<?php

namespace MattJanssen\ApiResponseBundle\Annotation;

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
class ApiResponse extends ConfigurationAnnotation
{
    /**
     * Optional Groups Names for JMS Serializer
     *
     * @var string[]
     */
    private $groups = [];

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
}
