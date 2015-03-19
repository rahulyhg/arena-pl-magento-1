<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetProducts;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetProductsTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetProducts
     */
    protected $getProducts;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getProducts = new GetProducts($this->clientMock);
    }

    public function testPathCorrectness()
    {
        $this->assertSame('/api/products', $this->getProducts->getPath());
        $this->assertSame('GET', $this->getProducts->getMethod());
        $this->assertSame([], $this->getProducts->getQuery());
    }

    public function testGetProducts()
    {
        $response = $this->getFileBasedResponse('get_products_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getProducts))
            ->willReturn($response);

        $products = $this->getProducts->getResult();

        $this->assertCount(16, $products);
        $this->assertSame(880258, $products[0]['id']);
        $this->assertSame('Macbook Pro Retina', $products[0]['name']);
        $this->assertSame(880274, $products[15]['id']);
        $this->assertSame('Szczotka do włosów', $products[15]['name']);
    }

    public function testMetadata()
    {
        $response = $this->getFileBasedResponse('get_products_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getProducts))
            ->willReturn($response);

        $this->getProducts->getResult();

        $this->assertSame(16, $this->getProducts->getCount());
        $this->assertSame(16, $this->getProducts->getTotalCount());
        $this->assertSame(1, $this->getProducts->getCurrentPage());
        $this->assertSame(25, $this->getProducts->getPerPage());
        $this->assertSame(1, $this->getProducts->getPages());
    }

    public function testWrongSetPageParamType()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->getProducts->setPage('im not a number');
    }

    public function testWrongSetResultsPerPageParamType()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->getProducts->setResultsPerPage('im not a number');
    }

    public function testInvalidSortOrderField()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->getProducts->setSort('name', 'bad order');
    }

    public function testInvalidSearchMethod()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->getProducts->setSearch('field', 'value', 'wrong method');
    }

    public function testCountableInterface()
    {
        $response = $this->getFileBasedResponse('get_products_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getProducts))
            ->willReturn($response);

        $this->assertCount(16, $this->getProducts);
    }

    public function testIteratorAggregate()
    {
        $response = $this->getFileBasedResponse('get_products_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getProducts))
            ->willReturn($response);

        $array = iterator_to_array($this->getProducts);

        $this->assertCount(16, $array);
    }
}
