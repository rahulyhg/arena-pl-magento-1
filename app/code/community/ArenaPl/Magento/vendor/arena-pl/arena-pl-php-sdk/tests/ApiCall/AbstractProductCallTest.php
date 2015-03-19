<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;
use ArenaPl\Test\ReflectionToolsTrait;

class AbstractProductCallTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;
    use ReflectionToolsTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractProductCallMock;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->abstractProductCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\AbstractProductCall')
            ->setConstructorArgs([$this->clientMock])
            ->setMethods(['initMetadataHelper'])
            ->getMock();
    }

    public function testEmptyProductSlugWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/empty/i');

        $this->abstractProductCallMock->setProductSlug('');
    }

    public function testBuildPathWillThrowExceptionWhenIdAndSlugSet()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/both/i');

        $reflectionObject = new \ReflectionObject($this->abstractProductCallMock);

        $productId = $reflectionObject->getProperty('productId');
        $productId->setAccessible(true);
        $productId->setValue($this->abstractProductCallMock, 12345);

        $productSlug = $reflectionObject->getProperty('productSlug');
        $productSlug->setAccessible(true);
        $productSlug->setValue($this->abstractProductCallMock, 'test-slug');

        $this->invokeNonPublicObjectMethod($this->abstractProductCallMock, 'buildPath');
    }
}
