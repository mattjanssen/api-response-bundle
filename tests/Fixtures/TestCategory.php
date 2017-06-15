<?php

namespace MattJanssen\ApiResponseBundle\Test\Fixtures;

use JMS\Serializer\Annotation as Serializer;
use MattJanssen\ApiResponseBundle\Serializer\JsonGroupSerializable;

/**
 * Test Category Fixture
 *
 * @Serializer\ExclusionPolicy("all")
 */
class TestCategory implements JsonGroupSerializable
{
    /**
     * @Serializer\Expose()
     *
     * @var int
     */
    private $id = 42;

    /**
     * @Serializer\Expose()
     *
     * @var string
     */
    private $name = 'foobar';

    /**
     * @var string
     */
    private $secret = 'dirty';

    /**
     * @Serializer\Expose()
     * @Serializer\Groups("relationships")
     * @Serializer\Accessor(getter="getParentId")
     *
     * @var TestCategory|null
     */
    private $parent;

    /**
     * @Serializer\Expose()
     * @Serializer\Groups("relationships")
     * @Serializer\Accessor(getter="getChildrenIds")
     *
     * @var TestCategory[]
     */
    private $children = [];

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'parent' => $this->getParentId(),
            'children' => $this->getChildrenIds(),
        ];
    }

    public function jsonGroupSerialize(array $groups = null)
    {
        $data = [
            'id' => $this->getId(),
            'name' => $this->getName(),
        ];

        if ($groups !== null && array_intersect($groups, ['relationships'])) {
            $data['parent'] = $this->getParentId();
            $data['children'] = $this->getChildrenIds();
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return TestCategory|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parent ? $this->parent->getId() : null;
    }

    /**
     * @param TestCategory|null $parent
     *
     * @return $this
     */
    public function setParent(TestCategory $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return TestCategory[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return int[]
     */
    public function getChildrenIds()
    {
        return array_map(function (TestCategory $child) { return $child->getId(); }, $this->getChildren());
    }

    /**
     * @param TestCategory[] $children
     *
     * @return $this
     */
    public function setChildren(array $children)
    {
        $this->children = $children;

        return $this;
    }
}
