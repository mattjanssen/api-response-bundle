<?php

namespace MattJanssen\ApiResponseBundle\Serializer;

/**
 * @see ArraySerializerAdapter
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface ArraySerializable
{
    /**
     * Return Array
     *
     * @param array $group
     * @return string[]
     */
    public function arraySerialize(array $group = []): array;
}
