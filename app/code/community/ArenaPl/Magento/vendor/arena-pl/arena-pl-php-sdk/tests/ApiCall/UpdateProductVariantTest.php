<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\UpdateProductVariant;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class UpdateProductVariantTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var UpdateProductVariant
     */
    protected $updateProductVariant;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->updateProductVariant = new UpdateProductVariant($this->clientMock);
    }

    public function testPathBuild()
    {
        $this->updateProductVariant->setProductId(567);
        $this->updateProductVariant->setProductVariantId(789);

        $this->assertSame('/api/products/567/variants/789', $this->updateProductVariant->getPath());
        $this->assertSame(ApiCallInterface::METHOD_PUT, $this->updateProductVariant->getMethod());
    }

    public function testVariantData()
    {
        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->callback(function (UpdateProductVariant $closure) {
                $this->assertSame($this->updateProductVariant, $closure);

                $this->assertSame([
                    'variant' => [
                        'option_value_ids' => '4,5,6,7',
                    ],
                ], $closure->getBody());

                return $closure->getMethod() === ApiCallInterface::METHOD_PUT;
            }))
            ->willReturn($response);

        $this->updateProductVariant->setProductId(567);
        $this->updateProductVariant->setProductVariantId(789);
        $this->updateProductVariant->setOptionValueIds([4, 5, 6]);
        $this->updateProductVariant->addOptionValueId(7);
        $this->updateProductVariant->addOptionValueId(7);

        $result = $this->updateProductVariant->getResult();
        $this->assertSame([
            'create' => 'ok',
        ], $result);
    }

    /**
     * @dataProvider pricesToCheckNormalization
     */
    public function testPriceWillGetNormalizedAfterSet($price, $priceExpectation)
    {
        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $callback = function (UpdateProductVariant $closure) use ($priceExpectation) {
            $this->assertSame([
                'variant' => [
                    'price' => $priceExpectation,
                ],
            ], $closure->getBody());

            return $closure->getMethod() === ApiCallInterface::METHOD_PUT;
        };

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->callback($callback))
            ->willReturn($response);

        $this->updateProductVariant->setVariantField('price', $price);
        $this->updateProductVariant->getResult();
    }

    public function pricesToCheckNormalization()
    {
        return [
            [5,'5,00'],
            [123.45,'123,45'],
            ['456', '456,00'],
            [789,'789,00'],
        ];
    }

    public function testFieldNormalizers()
    {
        $response = $this->getStringBasedResponse(json_encode(['create' => 'ok']));

        $this->clientMock
            ->method('makeAPICall')
            ->willReturn($response);

        $this->updateProductVariant->setVariantData([
            'pRiCe' => 9,
            'CUSTOM_field' => '!working',
        ]);

        $this->updateProductVariant->getResult();

        $this->assertSame([
            'variant' => [
                'price' => '9,00',
                'custom_field' => '!working',
            ],
        ], $this->updateProductVariant->getBody());
    }
}
