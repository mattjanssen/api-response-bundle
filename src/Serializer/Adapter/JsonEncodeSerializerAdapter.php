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

        $jsonError = json_last_error();

        if ($jsonError !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf(
                'JsonEncodeSerializerAdapter failed to serialize due to json_serialize error %s.',
                $jsonError
            ));
        }

        return $jsonString;
    }
}
