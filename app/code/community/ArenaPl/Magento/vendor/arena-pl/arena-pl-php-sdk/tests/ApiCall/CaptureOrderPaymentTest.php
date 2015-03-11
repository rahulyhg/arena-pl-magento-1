<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\CaptureOrderPayment;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;
use ArenaPl\Test\ReflectionToolsTrait;

class CaptureOrderPaymentTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;
    use ReflectionToolsTrait;

    /**
     * @var CaptureOrderPayment
     */
    protected $captureOrderPayment;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->captureOrderPayment = new CaptureOrderPayment($this->clientMock);
    }

    public function testNotSetPaymentIdWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/payment id/i');

        $this->captureOrderPayment->getPath();
    }

    public function testPathBuild()
    {
        $this->captureOrderPayment->setOrderNumber('A111');
        $this->captureOrderPayment->setPaymentId(12345);

        $this->assertSame(
            '/api/orders/A111/payments/12345/capture',
            $this->captureOrderPayment->getPath()
        );
    }
}
