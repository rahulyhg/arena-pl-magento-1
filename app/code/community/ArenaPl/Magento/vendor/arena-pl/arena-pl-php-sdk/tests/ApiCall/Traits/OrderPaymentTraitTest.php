<?php

namespace ArenaPl\Test\ApiCall\Traits;

use ArenaPl\ApiCall\Traits\OrderPaymentTrait;

class OrderPaymentTraitTest extends \PHPUnit_Framework_TestCase
{
    use OrderPaymentTrait;

    public function testNumericPaymentIdIsProperlySet()
    {
        $this->setPaymentId(12345);

        $this->assertSame(12345, $this->paymentId);
    }

    public function testNonNumericPaymentWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/numeric/i');

        $this->setPaymentId('im non numeric');
    }
}
