<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\CreateProductVariant;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class CreateProductVariantTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var CreateProductVariant
     */
    protected $createProductVariant;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->createProductVariant = new CreateProductVariant($this->clientMock);
    }

    public function testRequestParams()
    {
        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->createProductVariant))
            ->willReturn($response);

        $this->createProductVariant->setProductId(123);
        $this->createProductVariant->setVariantField('price', 3.99);
        $this->createProductVariant->setOptionValueIds([456, 789]);

        $this->assertSame(
            ApiCallInterface::METHOD_POST,
            $this->createProductVariant->getMethod()
        );

        $this->assertSame(
            '/api/products/123/variants',
            $this->createProductVariant->getPath()
        );

        $this->createProductVariant->getResult();

        $this->assertSame([
            'variant[option_value_ids]' => '456,789',
            'variant[price]' => '3,99',
        ], $this->createProductVariant->getQuery());
    }

    public function testFieldNormalizers()
    {
        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $this->clientMock
            ->method('makeAPICall')
            ->willReturn($response);

        $this->createProductVariant->setVariantData([
            'pRiCe' => 1.23,
            'CUSTOM_FIELD' => 'working',
        ]);

        $this->createProductVariant->getResult();

        $this->assertSame([
             'variant[price]' => '1,23',
             'variant[custom_field]' => 'working',
        ], $this->createProductVariant->getQuery());
    }
}
