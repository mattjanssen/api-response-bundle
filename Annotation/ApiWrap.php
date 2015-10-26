<?php

namespace MattJanssen\ApiWrapBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Annotation for Returning an API Wrap Response
 *
 * To produce a successful response, return an array or object that will be serialized
 * into the 'data' field of the API response.
 * To produce an error response, throw an ApiWrapException from the controller action.
 *
 * @Annotation
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiWrap extends ConfigurationAnnotation
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
        return 'api_wrap';
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
