<?php

namespace MattJanssen\ApiResponseBundle\Serializer\Adapter;

/**
 * Interface for Serialization Adapters
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface SerializerAdapterInterface
{
    /**
     * Serialize Response Data into a JSON String
     *
     * @param mixed $data
     * @param string[]|null $groups
     *
     * @return string
     */
    public function serialize($data, array $groups = null);
}
