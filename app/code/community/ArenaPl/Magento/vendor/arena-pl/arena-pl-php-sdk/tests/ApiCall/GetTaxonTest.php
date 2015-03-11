<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetTaxon;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetTaxonTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetTaxon
     */
    protected $getTaxon;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getTaxon = new GetTaxon($this->clientMock);
    }

    public function testGetPathWithNoParamsSetWillThrowException()
    {
        $this->setExpectedException('\RuntimeException');

        $this->getTaxon->getPath();
    }

    public function testCommandReturnsProperParams()
    {
        $this->getTaxon->setTaxonId(2);

        $this->assertSame('GET', $this->getTaxon->getMethod());
        $this->assertSame('/api/taxonomies/2', $this->getTaxon->getPath());
        $this->assertSame([], $this->getTaxon->getQuery());

        $this->getTaxon->setTaxonChildId(5);
        $this->assertSame('/api/taxonomies/2/taxons/5', $this->getTaxon->getPath());
    }

    public function testGetTaxon()
    {
        $response = $this->getFileBasedResponse('get_taxon_2.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getTaxon))
            ->willReturn($response);

        $taxon = $this->getTaxon->getResult();

        $this->assertSame($taxon['id'], 2);
        $this->assertSame($taxon['name'], 'Marka');
    }
}
