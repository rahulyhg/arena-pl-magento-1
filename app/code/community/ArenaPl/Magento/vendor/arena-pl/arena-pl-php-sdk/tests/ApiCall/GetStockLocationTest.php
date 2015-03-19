<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetStockLocation;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetStockLocationTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetStockLocation
     */
    protected $getStockLocation;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getStockLocation = new GetStockLocation($this->clientMock);
    }

    public function testPathThrowExceptionWhenStockLocationIdNotSet()
    {
        $this->setExpectedException('\RuntimeException');

        $this->getStockLocation->getPath();
    }

    public function testPathCorrectness()
    {
        $this->assertSame('GET', $this->getStockLocation->getMethod());
        $this->assertSame([], $this->getStockLocation->getQuery());

        $this->getStockLocation->setStockLocationId('5');
        $this->assertSame('/api/stock_locations/5', $this->getStockLocation->getPath());
        $this->assertSame([], $this->getStockLocation->getQuery());
    }

    public function testNonNumericStockLocationIdParamType()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->getStockLocation->setStockLocationId('im not a number');
    }
}
