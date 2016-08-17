<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * Trait to Implement ApiPathConfigInterface Methods
 *
 * @see ApiPathConfigInterface
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
trait ApiPathConfigTrait
{
    /**
     * Serializer Adapter to Use
     *
     * One of the Configuration::SERIALIZER_* constants.
     * @see MattJanssen\ApiResponseBundle\DependencyInjection\Configuration::SERIALIZER_JSON_ENCODE
     *
     * @var string
     */
    private $serializer;

    /**
     * @var string
     */
    private $corsAllowOriginRegex;

    /**
     * @var string[]
     */
    private $corsAllowHeaders = [];

    /**
     * @var int
     */
    private $corsMaxAge = 86400;

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
    public function getCorsAllowOriginRegex()
    {
        return $this->corsAllowOriginRegex;
    }

    /**
     * {@inheritdoc}
     */
    function isOriginAllowed($requestOrigin)
    {
        if (null === $this->corsAllowOriginRegex) {
            return false;
        }

        return preg_match('#' . str_replace('#', '\#', $this->corsAllowOriginRegex) . '#', $requestOrigin);
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
    public function setCorsAllowHeaders($corsAllowHeaders)
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
