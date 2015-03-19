<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetShippingCategories;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetShippingCategoriesTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetShippingCategories
     */
    protected $getShippingCategories;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getShippingCategories = new GetShippingCategories($this->clientMock);
    }

    public function testPathCorrectness()
    {
        $this->assertSame(
            '/api/shipping_categories',
            $this->getShippingCategories->getPath()
        );
        $this->assertSame('GET', $this->getShippingCategories->getMethod());
        $this->assertSame([], $this->getShippingCategories->getQuery());
    }

    public function testGetProducts()
    {
        $response = $this->getFileBasedResponse('get_shipping_categories_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getShippingCategories))
            ->willReturn($response);

        $categories = $this->getShippingCategories->getResult();

        $this->assertCount(3, $categories);
        $this->assertSame(648, $categories[1]['id']);
        $this->assertSame('B', $categories[1]['name']);
        $this->assertSame('2015-02-04T10:07:59.000+01:00', $categories[1]['created_at']);
        $this->assertSame('2015-02-04T10:07:59.000+01:00', $categories[1]['updated_at']);
        $this->assertSame(223, $categories[1]['tenant_id']);
    }

    public function testMetadata()
    {
        $response = $this->getFileBasedResponse('get_shipping_categories_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getShippingCategories))
            ->willReturn($response);

        $this->getShippingCategories->getResult();

        $this->assertSame(3, $this->getShippingCategories->getCount());
        $this->assertSame(1, $this->getShippingCategories->getCurrentPage());
        $this->assertSame(1, $this->getShippingCategories->getPages());
        $this->assertSame(3, $this->getShippingCategories->getTotalCount());
        $this->assertSame(25, $this->getShippingCategories->getPerPage());
    }

    public function testCountableInterface()
    {
        $response = $this->getFileBasedResponse('get_shipping_categories_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getShippingCategories))
            ->willReturn($response);

        $this->getShippingCategories->getResult();

        $this->assertCount(3, $this->getShippingCategories);
    }
}
