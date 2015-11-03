<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * Error Model Added to Failed API Responses
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseErrorModel implements \JsonSerializable
{
    /**
     * Application-specific API Error Code
     *
     * @var int
     */
    private $code;

    /**
     * Description of Error
     *
     * @var string
     */
    private $title;

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'title' => $this->title,
        ];
    }

    /**
     * Set the API Error Code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the API Error Code
     *
     * @param int $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set the API Error Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the API Error Title
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
