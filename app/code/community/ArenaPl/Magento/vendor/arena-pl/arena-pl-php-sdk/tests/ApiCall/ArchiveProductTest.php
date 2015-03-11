<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\ArchiveProduct;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class ArchiveProductTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var ArchiveProduct
     */
    protected $archiveProduct;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->archiveProduct = new ArchiveProduct($this->clientMock);
    }

    public function testPathCorrectlyBuilt()
    {
        $this->archiveProduct->setProductId(123);

        $this->assertSame('/api/products/123', $this->archiveProduct->getPath());
        $this->assertSame(ApiCallInterface::METHOD_DELETE, $this->archiveProduct->getMethod());
    }

    public function testPathCannotBeConstructedWithoutProductVariantSet()
    {
        $this->setExpectedException('\RuntimeException');

        $this->archiveProduct->getPath();
    }
}
