<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\GetOrders;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetOrdersTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetOrders
     */
    protected $getOrders;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getOrders = new GetOrders($this->clientMock);
    }

    public function testPathBuiltCorrectly()
    {
        $this->assertSame(ApiCallInterface::METHOD_GET, $this->getOrders->getMethod());

        $this->assertSame('/api/orders', $this->getOrders->getPath());
    }
}
