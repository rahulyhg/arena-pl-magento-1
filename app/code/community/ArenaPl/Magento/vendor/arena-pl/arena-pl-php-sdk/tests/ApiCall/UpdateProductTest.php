<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\UpdateProduct;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class UpdateProductTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var UpdateProduct
     */
    protected $updateProduct;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->updateProduct = new UpdateProduct($this->clientMock);
    }

    public function testProductData()
    {
        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->callback(function (UpdateProduct $closure) {
                $this->assertSame($this->updateProduct, $closure);

                $this->assertSame('/api/products/567', $closure->getPath());

                $this->assertSame([
                    'product[a]' => 'a val',
                    'product[b]' => 'b val',
                    'product[c]' => 'c val',
                ], $closure->getQuery());

                return $closure->getMethod() === ApiCallInterface::METHOD_PUT;
            }))
            ->willReturn($response);

        $this->updateProduct->setProductId(567);
        $this->updateProduct->setProductData([
            'a' => 'a val',
            'b' => 'b val',
        ]);
        $this->updateProduct->setProductField('c', 'c val');

        $result = $this->updateProduct->getResult();
        $this->assertSame([
            'create' => 'ok',
        ], $result);
    }
}
