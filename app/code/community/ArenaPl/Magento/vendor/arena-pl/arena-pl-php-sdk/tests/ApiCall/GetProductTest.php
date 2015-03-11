<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetProduct;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetProductTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetProduct
     */
    protected $getProduct;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getProduct = new GetProduct($this->clientMock);
    }

    public function testPathThrowExceptionWhenIdOrSlugNotSet()
    {
        $this->setExpectedException('\RuntimeException');

        $this->getProduct->getPath();
    }

    public function testPathCorrectness()
    {
        $this->assertSame('GET', $this->getProduct->getMethod());
        $this->assertSame([], $this->getProduct->getQuery());

        $this->getProduct->setProductId('5');
        $this->assertSame('/api/products/5', $this->getProduct->getPath());
        $this->assertSame([], $this->getProduct->getQuery());

        $this->getProduct->setProductSlug('product-slug');
        $this->assertSame('/api/products/product-slug', $this->getProduct->getPath());
        $this->assertSame([], $this->getProduct->getQuery());
    }

    public function testNonNumericProductIdParamType()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->getProduct->setProductId('im not a number');
    }
}
