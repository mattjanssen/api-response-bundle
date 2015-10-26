<?php

namespace ApiWrapBundle\Serializer\Adapter;

/**
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
