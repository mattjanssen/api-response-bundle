<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * Interface for Getting Configuration Options
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface ApiConfigInterface
{
    /**
     * Get Serializer Adapter to Use
     *
     * @return string|null
     */
    public function getSerializer();

    /**
     * Get Serializer Groups to Use
     *
     * @return string[]|null
     */
    public function getGroups();

    /**
     * Get Regex that Origin Must Match Before Adding CORS Headers
     *
     * @return string|null
     */
    public function getCorsAllowOriginRegex();

    /**
     * Get List of "Allow" Headers to Include in CORS Response
     *
     * @return string[]|null
     */
    public function getCorsAllowHeaders();

    /**
     * Get Seconds to Allow Browser Caching of CORS Headers
     *
     * @return int|null
     */
    public function getCorsMaxAge();
}
