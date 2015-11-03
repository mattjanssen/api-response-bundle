<?php

namespace MattJanssen\ApiResponseBundle\Serializer\Adapter;

/**
 * Adapter for Serializing Using PHP json_encode
 *
 * Serialized classes should implement \JsonSerializable to use this method.
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class JsonEncodeSerializerAdapter implements SerializerAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, array $groups = [])
    {
        $jsonString = json_encode($data);

        return $jsonString;
    }
}
