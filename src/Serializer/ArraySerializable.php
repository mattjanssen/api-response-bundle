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
     * @param string[]|null $group
     * @return string[]
     */
    public function arraySerialize(array $group = null);
}
