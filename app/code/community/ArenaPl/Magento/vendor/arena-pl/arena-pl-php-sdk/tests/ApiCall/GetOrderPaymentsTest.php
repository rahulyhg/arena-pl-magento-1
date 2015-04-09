<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\GetOrderPayments;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetOrderPaymentsTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetOrderPayments
     */
    protected $getOrderPayments;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getOrderPayments = new GetOrderPayments($this->clientMock);
    }

    public function testPathBuiltCorrectly()
    {
        $this->assertSame(ApiCallInterface::METHOD_GET, $this->getOrderPayments->getMethod());

        $this->getOrderPayments->setOrderNumber(12345);

        $this->assertSame('/api/orders/12345/payments', $this->getOrderPayments->getPath());
    }

    public function testPathCannotBeBuiltWithoutOrderNumber()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/order.{1,}not set/i');

        $this->getOrderPayments->getPath();
    }
}
