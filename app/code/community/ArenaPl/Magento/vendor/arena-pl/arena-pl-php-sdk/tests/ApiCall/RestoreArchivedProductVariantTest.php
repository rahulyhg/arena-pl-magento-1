<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\RestoreArchivedProductVariant;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class RestoreArchivedProductVariantTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var RestoreArchivedProductVariant
     */
    protected $restoreArchivedProductVariant;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->restoreArchivedProductVariant = new RestoreArchivedProductVariant($this->clientMock);
    }

    public function testMethodIsPost()
    {
        $this->assertSame(ApiCallInterface::METHOD_POST, $this->restoreArchivedProductVariant->getMethod());
    }

    public function testPathCorrectlyBuilt()
    {
        $this->restoreArchivedProductVariant->setProductSlug('product-slug');
        $this->restoreArchivedProductVariant->setProductVariantId(12345);

        $this->assertSame(
            '/api/products/product-slug/variants/12345/restore',
            $this->restoreArchivedProductVariant->getPath()
        );
    }

    public function testPathCannotBeBuiltWithoutVariantId()
    {
        $this->restoreArchivedProductVariant->setProductSlug('product-slug');

        $this->setExpectedExceptionRegExp('\RuntimeException', '/variant id/i');

        $this->restoreArchivedProductVariant->getPath();
    }
}
