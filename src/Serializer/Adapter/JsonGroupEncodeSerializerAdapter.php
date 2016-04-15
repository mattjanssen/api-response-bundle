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
        return json_encode($this->groupSerialize($data, $groups));
    }

    /**
     * Create Serializable Array with Groups
     *
     * Re-implementation of json_encode's traversal, but uses JsonGroupSerializable interface first.
     *
     * @param mixed $data
     * @param string[] $groups
     *
     * @return mixed[] Serializable array for json_encode()
     */
    private function groupSerialize($data, array $groups)
    {
        if (is_array($data)) {
            return $this->serializeArray($data, $groups);
        }

        if (is_object($data)) {
            if ($data instanceof JsonGroupSerializable) {
                return $this->serializeArray($data->jsonGroupSerialize($groups), $groups);
            }

            if ($data instanceof \JsonSerializable) {
                return $this->serializeArray($data->jsonSerialize(), $groups);
            }

            return null;
        }

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
    private function serializeArray($array, $groups)
    {
        return array_map(
            function ($value) use ($groups) {
                return $this->groupSerialize($value, $groups);
            },
            $array
        );
    }
}
