<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\CreateProduct;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class CreateProductTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var CreateProduct
     */
    protected $createProduct;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->createProduct = new CreateProduct($this->clientMock);
    }

    public function testProductsDataShouldBeErasedAtconsecutiveCalls()
    {
        $this->assertSame([], $this->createProduct->getQuery());

        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));
        $response2 = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('makeAPICall')
            ->with($this->identicalTo($this->createProduct))
            ->willReturnOnConsecutiveCalls($response, $response2);

        $this->createProduct->setProductData([
            'field 1' => 'val 1',
            'field 2' => 'val 2',
        ]);

        $created = $this->createProduct->getResult();
        $this->assertSame(['create' => 'ok'], $created);

        $this->assertSame([
            'product[field 1]' => 'val 1',
            'product[field 2]' => 'val 2',
        ], $this->createProduct->getQuery());

        $this->createProduct->setProductData([
            'field 3' => 'val 3',
            'field 4' => 'val 4',
        ]);

        $created = $this->createProduct->getResult();
        $this->assertSame(['create' => 'ok'], $created);

        $this->assertSame([
            'product[field 3]' => 'val 3',
            'product[field 4]' => 'val 4',
        ], $this->createProduct->getQuery());
    }

    public function testProductDataCanBeAddedLater()
    {
        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->createProduct))
            ->willReturn($response);

        $this->createProduct->setProductData([
            'field 1' => 'val 1',
            'field 2' => 'val 2',
        ]);

        $this->createProduct->setProductField('field 3', 'val 3');

        $created = $this->createProduct->getResult();
        $this->assertSame(['create' => 'ok'], $created);

        $this->assertSame([
            'product[field 1]' => 'val 1',
            'product[field 2]' => 'val 2',
            'product[field 3]' => 'val 3',
        ], $this->createProduct->getQuery());
    }

    public function testSetProductFieldWillThrowExceptionOnEmptyName()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->createProduct->setProductField('', 'val');
    }
}
