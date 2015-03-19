<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\DeleteProductImage;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class DeleteProductImageTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var DeleteProductImage
     */
    protected $deleteProductImage;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->deleteProductImage = new DeleteProductImage($this->clientMock);
    }

    public function testNonNumericProductImageIdWillThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->deleteProductImage->setProductImageId('im not a number');
    }

    public function testPathBuiltCorrectly()
    {
        $this->deleteProductImage->setProductImageId(123)->setProductSlug('product-slug');

        $this->assertSame(
            '/api/products/product-slug/images/123',
            $this->deleteProductImage->getPath()
        );

        $this->deleteProductImage->setProductId(456);
        $this->assertSame(
            '/api/products/456/images/123',
            $this->deleteProductImage->getPath()
        );
    }

    public function testNotSetProductImageIdWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/image id/i');

        $this->deleteProductImage->getPath();
    }
}
