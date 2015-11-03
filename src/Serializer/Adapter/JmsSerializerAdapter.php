<?php

namespace MattJanssen\ApiResponseBundle\Serializer\Adapter;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * Adapter for Serializing Using JMS Serializer
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class JmsSerializerAdapter implements SerializerAdapterInterface
{
    /**
     * @var SerializerInterface
     */
    private $jmsSerializer;

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->jmsSerializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, array $groups = [])
    {
        $context = SerializationContext::create();

        if ($groups) {
            $context->setGroups($groups);
        }

        $jsonString = $this->jmsSerializer->serialize(
            $data,
            'json',
            $context
        );

        return $jsonString;
    }
}
