<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\GetStockItems;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetStockItemsTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetStockItems
     */
    protected $getStockItems;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getStockItems = new GetStockItems($this->clientMock);
    }

    public function testRequestParams()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->callback(function (GetStockItems $closure) {
                $this->assertSame($this->getStockItems, $closure);

                $this->assertSame('/api/stock_locations/12345/stock_items', $closure->getPath());

                $this->assertSame([
                    'page' => 5,
                    'per_page' => 25,
                    'q[s]' => 'order field desc',
                    'q[search field_eq]' => 'search value',
                ], $closure->getQuery());

                return $closure->getMethod() === ApiCallInterface::METHOD_GET;
            }))
            ->willReturn($this->getStringBasedResponse(json_encode([
                'result' => 'ok',
                'stock_items' => ['im stock' => ['item1', 'item2']],
                'pages' => 5,
            ])));

        $this->getStockItems->setPage(5);
        $this->getStockItems->setResultsPerPage(25);
        $this->getStockItems->setSearch('search field', 'search value', ApiCallInterface::SEARCH_METHOD_EQUALS);
        $this->getStockItems->setSort('order field', ApiCallInterface::SORT_DESC);
        $this->getStockItems->setStockLocationId(12345);

        $this->assertSame([
            'im stock' => [
                'item1',
                'item2',
            ],
        ], $this->getStockItems->getResult());

        $this->assertSame(5, $this->getStockItems->getPages());
        $this->assertNull($this->getStockItems->getCount());
        $this->assertNull($this->getStockItems->getCurrentPage());
    }

    public function testMalformedResponseWillThrowException()
    {
        $this->setExpectedException('\ArenaPl\Exception\ApiCallException');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->willReturn($this->getStringBasedResponse(json_encode([
                'im' => 'malformed response',
            ])));

        $this->getStockItems->getResult();
    }
}
