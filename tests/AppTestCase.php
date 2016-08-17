<?php

namespace MattJanssen\ApiResponseBundle\Test;

use Doctrine\Common\Annotations\AnnotationRegistry;

class AppTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        AnnotationRegistry::registerAutoloadNamespace('JMS\Serializer\Annotation', 'vendor/jms/serializer/src');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * Asserts a given array and a given JSON are equal.
     *
     * @param array $expectedArray
     * @param string $actualJson
     * @param string $message
     */
    public static function assertJsonStringEqualsArray($expectedArray, $actualJson, $message = '')
    {
        self::assertJson($actualJson, $message);

        $actual = json_decode($actualJson, true);

        self::assertEquals($expectedArray, $actual, $message);
    }
}
