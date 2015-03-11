<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\GetStockItem;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetStockItemTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetStockItem
     */
    protected $getStockItem;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getStockItem = new GetStockItem($this->clientMock);
    }

    public function testRequestParams()
    {
        $this->assertSame(
            ApiCallInterface::METHOD_GET,
            $this->getStockItem->getMethod()
        );

        $this->getStockItem->setStockLocationId(5);
        $this->getStockItem->setStockItemId(10);

        $this->assertSame(
            '/api/stock_locations/5/stock_items/10',
            $this->getStockItem->getPath()
        );
    }

    public function testPathWithoutStockItemIdWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/id not set/i');

        $this->getStockItem->getPath();
    }
}
