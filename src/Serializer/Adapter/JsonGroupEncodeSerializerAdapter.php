<?php

namespace MattJanssen\ApiResponseBundle\Serializer\Adapter;

use MattJanssen\ApiResponseBundle\Serializer\JsonGroupSerializable;

/**
 * Adapter for Group-aware Serialization Using PHP json_encode
 *
 * Serialized classes should implement JsonGroupSerializable or \JsonSerializable.
 *
 * @see JsonGroupSerializable
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class JsonGroupEncodeSerializerAdapter implements SerializerAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, array $groups = [])
    {
        $jsonString = json_encode($this->groupSerialize($data, $groups));

        $jsonError = json_last_error();

        if ($jsonError !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf(
                'JsonEncodeSerializerAdapter failed to serialize due to json_serialize error %s.',
                $jsonError
            ));
        }

        return $jsonString;
    }

    /**
     * Create Serializable Array with Groups
     *
     * Re-implementation of json_encode's traversal, but uses JsonGroupSerializable interface first.
     *
     * @param mixed $data
     * @param string[] $groups
     *
     * @return \stdClass|mixed[] Serializable array for json_encode()
     */
    private function groupSerialize($data, array $groups)
    {
        if (is_array($data)) {
            return $this->serializeArray($data, $groups);
        }

        if (is_object($data)) {
            if ($data instanceof JsonGroupSerializable) {
                return $this->groupSerialize($data->jsonGroupSerialize($groups), $groups);
            }

            if ($data instanceof \JsonSerializable) {
                return $this->groupSerialize($data->jsonSerialize(), $groups);
            }

            // A non-serializable object returns as an empty object.
            // This gets converted to {} by json_encode, where as an empty array is converted to [].
            return new \stdClass();
        }

        // Scalar data is returned as-is.
        return $data;
    }

    /**
     * Map Array Back to Recursive Group Serialize Function
     *
     * @param mixed[] $array
     * @param string[] $groups
     *
     * @return mixed[]
     */
    private function serializeArray(array $array, $groups)
    {
        return array_map(
            function ($value) use ($groups) {
                return $this->groupSerialize($value, $groups);
            },
            $array
        );
    }
}
