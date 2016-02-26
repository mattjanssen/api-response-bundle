<?php

namespace MattJanssen\ApiResponseBundle\Model;

/**
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface ApiPathConfigInterface
{
    public function getCorsAllowOriginRegex();

    public function getCorsAllowHeaders();

    public function getCorsMaxAge();
}
