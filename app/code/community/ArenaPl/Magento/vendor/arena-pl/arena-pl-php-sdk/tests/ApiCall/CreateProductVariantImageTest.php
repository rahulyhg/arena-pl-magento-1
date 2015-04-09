<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\CreateProductVariantImage;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class CreateProductVariantImageTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var CreateProductVariantImage
     */
    protected $createProductVariantImage;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->createProductVariantImage = new CreateProductVariantImage($this->clientMock);
    }

    public function testProductUrlWillSetBodyParams()
    {
        $this->assertSame([], $this->createProductVariantImage->getBody());
        $this->assertSame([], $this->createProductVariantImage->getQuery());

        $this->createProductVariantImage->setProductVariantImageUrl('http://test.url');

        $this->assertSame([
            'url' => 'http://test.url',
        ], $this->createProductVariantImage->getBody());
        $this->assertSame([], $this->createProductVariantImage->getQuery());
    }

    public function testPathIsProperlyBuilt()
    {
        $this->createProductVariantImage->setProductVariantId(987);
        $this->assertSame(
            '/api/variants/987/images/create_from_url',
            $this->createProductVariantImage->getPath()
        );

        $this->assertSame(
            ApiCallInterface::METHOD_POST,
            $this->createProductVariantImage->getMethod()
        );
    }

    public function testPathCannotBeBuildWithoutVariantId()
    {
        $this->setExpectedExceptionRegExp(
            '\RuntimeException',
            '/variant id not set/i'
        );

        $this->createProductVariantImage->getPath();
    }

    public function testEmptyProductImageUrlWillThrowException()
    {
        $this->setExpectedExceptionRegExp(
            '\InvalidArgumentException',
            '/empty url/i'
        );

        $this->createProductVariantImage->setProductVariantImageUrl('');
    }
}
