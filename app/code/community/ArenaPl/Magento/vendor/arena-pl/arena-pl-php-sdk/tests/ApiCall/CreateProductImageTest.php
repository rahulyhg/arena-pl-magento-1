<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\CreateProductImage;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class CreateProductImageTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var CreateProductImage
     */
    protected $createProductImage;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->createProductImage = new CreateProductImage($this->clientMock);
    }

    public function testProductUrlWillSetBodyParams()
    {
        $this->assertSame([], $this->createProductImage->getBody());
        $this->assertSame([], $this->createProductImage->getQuery());

        $this->createProductImage->setProductImageUrl('test url');

        $this->assertSame([
            'url' => 'test url',
        ], $this->createProductImage->getBody());
        $this->assertSame([], $this->createProductImage->getQuery());
    }

    public function testPathIsProperlyBuilt()
    {
        $this->createProductImage->setProductId(123);
        $this->assertSame('/api/products/123/images/create_from_url', $this->createProductImage->getPath());

        $this->createProductImage->setProductSlug('abc');
        $this->assertSame('/api/products/abc/images/create_from_url', $this->createProductImage->getPath());
    }

    public function testEmptyProductImageUrlWillThrowException()
    {
        $this->setExpectedExceptionRegExp(
            '\InvalidArgumentException',
            '/empty url/i'
        );

        $this->createProductImage->setProductImageUrl('');
    }
}
