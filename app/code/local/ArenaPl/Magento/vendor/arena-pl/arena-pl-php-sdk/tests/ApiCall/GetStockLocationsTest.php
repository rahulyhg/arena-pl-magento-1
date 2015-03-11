<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetStockLocations;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetStockLocationsTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetStockLocations
     */
    protected $getStockLocations;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getStockLocations = new GetStockLocations($this->clientMock);
    }

    public function testPathCorrectness()
    {
        $this->assertSame('/api/stock_locations', $this->getStockLocations->getPath());
        $this->assertSame('GET', $this->getStockLocations->getMethod());
        $this->assertSame([], $this->getStockLocations->getQuery());
    }

    public function testGetProducts()
    {
        $response = $this->getFileBasedResponse('get_stock_locations_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getStockLocations))
            ->willReturn($response);

        $stockLocations = $this->getStockLocations->getResult();

        $this->assertCount(1, $stockLocations);
        $this->assertSame(218, $stockLocations[0]['id']);
        $this->assertSame('test_location', $stockLocations[0]['name']);
    }

    public function testMetadata()
    {
        $response = $this->getFileBasedResponse('get_stock_locations_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getStockLocations))
            ->willReturn($response);

        $this->getStockLocations->getResult();

        $this->assertSame(1, $this->getStockLocations->getCount());
        $this->assertSame(1, $this->getStockLocations->getCurrentPage());
        $this->assertSame(1, $this->getStockLocations->getPages());
    }

    public function testCountableInterface()
    {
        $response = $this->getFileBasedResponse('get_stock_locations_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getStockLocations))
            ->willReturn($response);

        $this->assertCount(1, $this->getStockLocations);
    }
}
