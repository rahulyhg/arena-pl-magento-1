<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\RestoreArchivedProduct;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class RestoreArchivedProductTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var RestoreArchivedProduct
     */
    protected $restoreArchivedProduct;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->restoreArchivedProduct = new RestoreArchivedProduct($this->clientMock);
    }

    public function testPathCorrectlyBuilt()
    {
        $this->restoreArchivedProduct->setProductSlug('product-slug');

        $this->assertSame('/api/products/product-slug/restore', $this->restoreArchivedProduct->getPath());
        $this->assertSame(ApiCallInterface::METHOD_POST, $this->restoreArchivedProduct->getMethod());
    }
}
