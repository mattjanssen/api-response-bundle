<?php

namespace MattJanssen\ApiResponseBundle\Serializer;

/**
 * Interface for JsonSerializable Models with Group-based Serialization
 *
 * If used in conjunction with the JsonGroupEncodeSerializerAdapter, this pairs
 * PHP's internal json_encode with a group-aware serializer.
 * Models implementing this must still implement jsonSerialize().
 *
 * @see JsonGroupEncodeSerializerAdapter
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface JsonGroupSerializable extends \JsonSerializable
{
    /**
     * Return JSON Array Based on Groups
     *
     * @param string[]|null $groups
     *
     * @return mixed[]
     */
    public function jsonGroupSerialize(array $groups = null);
}
