<?php

namespace MattJanssen\ApiResponseBundle\Test\Serializer\Adapter;

use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonGroupEncodeSerializerAdapter;
use MattJanssen\ApiResponseBundle\Test\AppTestCase;

class JsonGroupEncodeSerializerAdapterTest extends AppTestCase
{
    public function testNonSerializableObject()
    {
        $adapter = new JsonGroupEncodeSerializerAdapter();

        $result = $adapter->serialize(new \stdClass());

        self::assertSame('{}', $result);
    }

    public function testJsonEncodeNanException()
    {
        $adapter = new JsonGroupEncodeSerializerAdapter();

        self::expectException(\RuntimeException::class);

        $adapter->serialize(acos(8));
    }
}
