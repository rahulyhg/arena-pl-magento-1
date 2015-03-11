<?php

namespace ArenaPl\Test\ApiCall\Traits;

use ArenaPl\ApiCall\Traits\ShipmentTrait;

class ShipmentTraitTest extends \PHPUnit_Framework_TestCase
{
    use ShipmentTrait;

    public function testShipmentIdIsProperlySet()
    {
        $this->setShipmentNumber('A12345');

        $this->assertSame('A12345', $this->shipmentNumber);
    }

    public function testEmptyShipmentWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/empty/i');

        $this->setShipmentNumber('');
    }
}
