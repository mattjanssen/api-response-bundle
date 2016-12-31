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
}
