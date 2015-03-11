<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;
use ArenaPl\Test\ReflectionToolsTrait;

class AbstractOrderCallTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;
    use ReflectionToolsTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractOrderCallMock;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->abstractOrderCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\AbstractOrderCall')
            ->setConstructorArgs([$this->clientMock])
            ->setMethods(['initMetadataHelper'])
            ->getMock();
    }

    public function testProperOrderNumber()
    {
        $this->abstractOrderCallMock->setOrderNumber('ABC12345');

        $this->assertSame(
            'ABC12345',
            $this->getNonPublicObjectProperty(
                $this->abstractOrderCallMock,
                'orderNumber'
            )
        );

        $this->abstractOrderCallMock->setOrderNumber(9876);
        $this->assertSame(
            '9876',
            $this->getNonPublicObjectProperty(
                $this->abstractOrderCallMock,
                'orderNumber'
            )
        );
    }

    public function testEmptyOrderNumber()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->abstractOrderCallMock->setOrderNumber('');
    }

    public function testNotSetOrderNumberWillThrowException()
    {
        $this->setExpectedException('\RuntimeException');

        $this->invokeNonPublicObjectMethod($this->abstractOrderCallMock, 'buildPath');
    }

    public function testBuildPath()
    {
        $this->abstractOrderCallMock->setOrderNumber(12345);

        $this->assertSame(
            '/api/orders/12345',
            $this->invokeNonPublicObjectMethod(
                $this->abstractOrderCallMock,
                'buildPath'
            )
        );
        $this->assertSame(
            '/api/orders/12345/asdf',
            $this->invokeNonPublicObjectMethod(
                $this->abstractOrderCallMock,
                'buildPath',
                '/asdf'
            )
        );
    }
}
