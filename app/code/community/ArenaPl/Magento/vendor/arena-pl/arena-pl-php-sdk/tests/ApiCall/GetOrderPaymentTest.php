<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetOrderPayment;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetOrderPaymentTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetOrderPayment
     */
    protected $getOrderPayment;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getOrderPayment = new GetOrderPayment($this->clientMock);
    }

    public function testNotSetPaymentIdWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/payment id/i');

        $this->getOrderPayment->getPath();
    }

    public function testPathBuild()
    {
        $this->getOrderPayment->setOrderNumber('A111');
        $this->getOrderPayment->setPaymentId(12345);

        $this->assertSame(
            '/api/orders/A111/payments/12345',
            $this->getOrderPayment->getPath()
        );
    }
}
