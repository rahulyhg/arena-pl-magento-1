<?php

namespace ArenaPl\Test\ApiCall\Traits;

use ArenaPl\ApiCall\Traits\StockItemTrait;

class StockItemTraitTest extends \PHPUnit_Framework_TestCase
{
    use StockItemTrait;

    public function testStockItemIsProperlySet()
    {
        $this->setStockItemId(12345);
        $this->assertSame(12345, $this->stockItemId);

        $this->setStockItemId('5678');
        $this->assertSame(5678, $this->stockItemId);
    }

    public function testNonNumericStockItemWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/non numeric/i');

        $this->setStockItemId('non numeric');
    }
}
