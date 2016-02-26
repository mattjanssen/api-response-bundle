<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiPathConfig implements ApiPathConfigInterface
{
    /**
     * @var string
     */
    private $corsAllowOriginRegex = false;

    /**
     * @var string[]
     */
    private $corsAllowHeaders = [];

    /**
     * @var int
     */
    private $corsMaxAge = 86400;

    /**
     * @return string
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
     * @return string[]
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
     * @return int
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
