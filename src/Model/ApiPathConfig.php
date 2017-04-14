<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiPathConfig extends ApiConfig
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     *
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }
}
