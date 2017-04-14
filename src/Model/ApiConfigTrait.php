<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * Trait to Implement ApiPathConfigInterface Methods
 *
 * @see ApiPathConfigInterface
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
trait ApiConfigTrait
{
    /**
     * Serializer Adapter to Use
     *
     * One of the Configuration::SERIALIZER_* constants or a service name.
     * @see MattJanssen\ApiResponseBundle\DependencyInjection\Configuration::SERIALIZER_JSON_ENCODE
     *
     * @var string
     */
    private $serializer;

    /**
     * Optional Groups Names for Serializer
     *
     * @var string[]|null
     */
    private $groups;

    /**
     * @var string
     */
    private $corsAllowOriginRegex;

    /**
     * @var string[]
     */
    private $corsAllowHeaders;

    /**
     * @var int
     */
    private $corsMaxAge; // One day in seconds.

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param string[]|null $groups
     *
     * @return $this
     */
    public function setGroups(array $groups = null)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCorsAllowOriginRegex()
    {
        return $this->corsAllowOriginRegex;
    }

    /**
     * @param string $corsAllowOriginRegex
     *
     * @return $this
     */
    public function setCorsAllowOriginRegex($corsAllowOriginRegex)
    {
        $this->corsAllowOriginRegex = $corsAllowOriginRegex;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCorsAllowHeaders()
    {
        return $this->corsAllowHeaders;
    }

    /**
     * @param string[] $corsAllowHeaders
     *
     * @return $this
     */
    public function setCorsAllowHeaders(array $corsAllowHeaders = null)
    {
        $this->corsAllowHeaders = $corsAllowHeaders;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCorsMaxAge()
    {
        return $this->corsMaxAge;
    }

    /**
     * @param int $corsMaxAge
     *
     * @return $this
     */
    public function setCorsMaxAge($corsMaxAge)
    {
        $this->corsMaxAge = $corsMaxAge;

        return $this;
    }
}
