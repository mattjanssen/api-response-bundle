<?php

namespace MattJanssen\ApiResponseBundle\Serializer\Adapter;

use MattJanssen\ApiResponseBundle\Serializer\ArraySerializable;

/**
 * Adapter for Serialization Using PHP json_encode
 *
 * Serialized classes should implement ArraySerializable.
 *
 * @see ArraySerializable
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ArraySerializerAdapter implements SerializerAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data, array $groups = [])
    {
        $jsonString = json_encode($this->serializedMixed($data, $groups));

        $jsonError = json_last_error();

        if ($jsonError !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf(
                'ArraySerializerAdapter failed to serialize due to json_serialize error %s.',
                $jsonError
            ));
        }

        return $jsonString;
    }

    /**
     * Create Serializable Array
     *
     * Similar to json_encode's traversal, but uses ArraySerializable interface.
     *
     * @param mixed $data
     * @param string[] $groups
     *
     * @return \stdClass|mixed[] Serializable array for json_encode()
     */
    private function serializedMixed($data, array $groups)
    {
        if (is_array($data)) {
            return $this->serializeArray($data, $groups);
        }

        if (is_object($data)) {
            if ($data instanceof ArraySerializable) {
                return $this->serializedMixed($data->arraySerialize($groups), $groups);
            }

            // A non-serializable object returns as an empty \stdClass object.
            // This gets converted to {} by json_encode, where as an empty array is converted to [].
            return new \stdClass();
        }

        // Scalar data is returned as-is.
        return $data;
    }

    /**
     * Map Array Back to Recursive Serialize Function
     *
     * @param mixed[] $array
     * @param string[] $groups
     *
     * @return mixed[]
     */
    private function serializeArray(array $array, array $groups)
    {
        return array_map(
            function ($value) use ($groups) {
                return $this->serializedMixed($value, $groups);
            },
            $array
        );
    }
}
