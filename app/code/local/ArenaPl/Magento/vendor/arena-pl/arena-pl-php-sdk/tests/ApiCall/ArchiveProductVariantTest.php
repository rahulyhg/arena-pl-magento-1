<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\ArchiveProductVariant;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class ArchiveProductVariantTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var ArchiveProductVariant
     */
    protected $archiveProductVariant;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->archiveProductVariant = new ArchiveProductVariant($this->clientMock);
    }

    public function testPathCorrectlyBuilt()
    {
        $this->archiveProductVariant->setProductId(123);
        $this->archiveProductVariant->setProductVariantId(456);

        $this->assertSame('/api/products/123/variants/456', $this->archiveProductVariant->getPath());
        $this->assertSame(ApiCallInterface::METHOD_DELETE, $this->archiveProductVariant->getMethod());
    }

    public function testPathCannotBeConstructedWithoutProductVariantSet()
    {
        $this->archiveProductVariant->setProductId(123);

        $this->setExpectedException('\RuntimeException');

        $this->archiveProductVariant->getPath();
    }
}
