<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallFactory;

class ApiCallFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiCallFactory
     */
    protected $apiCallFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    protected function setUp()
    {
        $this->clientMock = $this->getMockBuilder('\ArenaPl\ApiCallExecutor\ApiCallExecutorInterface')->getMock();

        $this->apiCallFactory = new ApiCallFactory($this->clientMock);
    }

    public function testNonExistingClassWillThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->apiCallFactory->getApiCall('NonExistingClass');
    }

    public function testClassNotImplementingInterfaceWillThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->apiCallFactory->getApiCall('AbstractApiCall');
    }

    public function testProperClassWillReturnConfiguredObject()
    {
        $result = $this->apiCallFactory->getApiCall('GetProducts');

        $this->assertInstanceOf('\ArenaPl\ApiCall\GetProducts', $result);
    }

    public function testClientIsPassedToConstructedObject()
    {
        require_once __DIR__ . '/ApiCallToCheckExecutor.php';

        $result = $this->apiCallFactory->getApiCall('ApiCallToCheckExecutor');

        $this->assertInstanceOf('\ArenaPl\ApiCall\ApiCallToCheckExecutor', $result);
        $this->assertSame($this->clientMock, $result->getApiCallExecutor());
    }
}
