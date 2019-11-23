<?php

namespace MattJanssen\ApiResponseBundle\Test\Serializer\Adapter;

use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonEncodeSerializerAdapter;
use MattJanssen\ApiResponseBundle\Test\AppTestCase;

class JsonEncodeSerializerAdapterTest extends AppTestCase
{
    public function testJsonEncodeNanException()
    {
        $adapter = new JsonEncodeSerializerAdapter();

        self::expectException(\RuntimeException::class);

        $adapter->serialize(acos(8));
    }
}
