<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetTaxonTree;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetTaxonTreeTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetTaxonTree
     */
    protected $getTaxonTree;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getTaxonTree = new GetTaxonTree($this->clientMock);
    }

    public function testNonNumericTaxonIdWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/non numeric/i');

        $this->getTaxonTree->setTaxonId('garbage');
    }

    public function testNonNumericTaxonChildIdWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/non numeric/i');

        $this->getTaxonTree->setTaxonChildId('also garbage');
    }

    public function testPathWillNotBeGeneratedIfTaxonIdNotSet()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/taxon id/i');

        $this->getTaxonTree->getPath();
    }

    public function testPathWillNotBeGeneratedIfTaxonChildIdNotSet()
    {
        $this->getTaxonTree->setTaxonId(12345);

        $this->setExpectedExceptionRegExp('\RuntimeException', '/taxon child id/i');

        $this->getTaxonTree->getPath();
    }

    public function testPathIsCorrect()
    {
        $this->getTaxonTree->setTaxonId(111);
        $this->getTaxonTree->setTaxonChildId(222);

        $this->assertSame(
            '/api/taxonomies/111/taxons/222/tree',
            $this->getTaxonTree->getPath()
        );
    }
}
