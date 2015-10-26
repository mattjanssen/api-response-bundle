<?php

namespace MattJanssen\ApiWrapBundle\Serializer\Adapter;

/**
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface SerializerAdapterInterface
{
    public function serialize($data, array $groups = []);
}
