<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\SetProductProperty;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class SetProductPropertyTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var SetProductProperty
     */
    protected $setProductProperty;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->setProductProperty = new SetProductProperty($this->clientMock);
    }

    public function testNonNumericProductPropertyWillThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->setProductProperty->setProductPropertyId('im not numeric');
    }

    public function testUnableToBuildPathWithoutProductProperty()
    {
        $this->setExpectedException('\RuntimeException');

        $this->setProductProperty->getPath();
    }

    public function testPathIsProperlyBuilt()
    {
        $this->setProductProperty->setProductPropertyId(987);

        $this->setProductProperty->setProductId(123);
        $this->assertSame('/api/products/123/product_properties/987', $this->setProductProperty->getPath());

        $this->setProductProperty->setProductSlug('abc');
        $this->assertSame('/api/products/abc/product_properties/987', $this->setProductProperty->getPath());
    }

    public function testPropertyValueSetsBody()
    {
        $this->assertSame([], $this->setProductProperty->getBody());

        $this->setProductProperty->setPropertyValue('test value');

        $this->assertSame([
            'product_property' => [
                'value' => 'test value',
            ],
        ], $this->setProductProperty->getBody());
    }
}
