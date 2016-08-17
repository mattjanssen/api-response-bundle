<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * Interface for Getting Configuration Options
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface ApiPathConfigInterface
{
    /**
     * Get Serializer Adapter to Use
     *
     * @return string
     */
    public function getSerializer();

    /**
     * Get Regex that Origin Must Match Before Adding CORS Headers
     *
     * @return string
     */
    public function getCorsAllowOriginRegex();

    /**
     * Check URL Against Configured Allowed Origin Regex
     *
     * @return string
     */
    public function isOriginAllowed($requestOrigin);

    /**
     * Get List of "Allow" Headers to Include in CORS Response
     *
     * @return string[]
     */
    public function getCorsAllowHeaders();

    /**
     * Get Seconds to Allow Browser Caching of CORS Headers
     *
     * @return int
     */
    public function getCorsMaxAge();
}
