<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\SetProductRelatedProducts;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;
use GuzzleHttp\Message\Response;

class SetProductRelatedProductsTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var SetProductRelatedProducts
     */
    protected $setProductRelatedProducts;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->setProductRelatedProducts = new SetProductRelatedProducts($this->clientMock);
    }

    public function testSetRelatedProducts()
    {
        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->callback(function (SetProductRelatedProducts $closure) {
                $this->assertSame($this->setProductRelatedProducts, $closure);

                $this->assertSame([
                    'ids' => '1,2,3,4',
                ], $closure->getQuery());

                return $closure->getMethod() === ApiCallInterface::METHOD_PUT;
            }))
            ->willReturn(new Response(200));

        $this->setProductRelatedProducts->setProductId(999);
        $this->setProductRelatedProducts->setRelatedProductIds([1, 2, 3]);
        $this->setProductRelatedProducts->addRelatedProductId(4);
        $this->setProductRelatedProducts->addRelatedProductId(4);

        $this->assertSame('/api/products/999/relate', $this->setProductRelatedProducts->getPath());

        $this->assertTrue($this->setProductRelatedProducts->getResult());
    }

    public function testNonNumericSetRelatedProductIds()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->setProductRelatedProducts->setRelatedProductIds(['5', 'non numeric']);
    }

    public function testNonNumericAddRelatedProductId()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->setProductRelatedProducts->addRelatedProductId('non numeric');
    }
}
