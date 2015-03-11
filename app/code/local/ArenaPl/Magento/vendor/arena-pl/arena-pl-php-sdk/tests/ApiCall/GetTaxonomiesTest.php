<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\GetTaxonomies;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class GetTaxonomiesTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var GetTaxonomies
     */
    protected $getTaxonomies;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->getTaxonomies = new GetTaxonomies($this->clientMock);
    }

    public function testCommandReturnsProperParams()
    {
        $this->getTaxonomies->setPage(2);
        $this->getTaxonomies->setResultsPerPage(5);
        $this->getTaxonomies->setSearch('name', 'search value');
        $this->getTaxonomies->setSort('name', 'asc');

        $this->assertSame('GET', $this->getTaxonomies->getMethod());
        $this->assertSame('/api/taxonomies', $this->getTaxonomies->getPath());
        $query = $this->getTaxonomies->getQuery();
        ksort($query);
        $this->assertSame([
            'page' => 2,
            'per_page' => 5,
            'q[name_cont]' => 'search value',
            'q[s]' => 'name asc',
        ], $query);
    }

    public function testGetTaxonomiesWithNoParams()
    {
        $response = $this->getFileBasedResponse('get_taxonomies_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getTaxonomies))
            ->willReturn($response);

        $taxonomies = $this->getTaxonomies->getResult();

        $this->assertCount(3, $taxonomies);
        $this->assertSame($taxonomies[0]['id'], 3);
        $this->assertSame($taxonomies[0]['name'], 'Gorące Oferty');

        $this->assertSame(3, $this->getTaxonomies->getCount());
        $this->assertSame(0, $this->getTaxonomies->getCurrentPage());
        $this->assertSame(1, $this->getTaxonomies->getPages());
    }

    public function testRawResponseIsIdenticalToClientResponse()
    {
        $this->assertNull($this->getTaxonomies->getRawResponse());

        $response = $this->getFileBasedResponse('get_taxonomies_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getTaxonomies))
            ->willReturn($response);

        $this->getTaxonomies->getResult();

        $this->assertSame($response, $this->getTaxonomies->getRawResponse());
    }

    public function testGetTaxonomiesWithParams()
    {
        $response = $this->getFileBasedResponse('get_taxonomies_2_2_name_desc.txt');

        $this->getTaxonomies->setPage(2);
        $this->getTaxonomies->setResultsPerPage(2);
        $this->getTaxonomies->setSort('name', 'desc');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getTaxonomies))
            ->willReturn($response);

        $taxonomies = $this->getTaxonomies->getResult();

        $this->assertCount(1, $taxonomies);
        $this->assertSame($taxonomies[0]['id'], 3);
        $this->assertSame($taxonomies[0]['name'], 'Gorące Oferty');

        $this->assertSame(1, $this->getTaxonomies->getCount());
        $this->assertSame(2, $this->getTaxonomies->getCurrentPage());
        $this->assertSame(2, $this->getTaxonomies->getPages());
    }

    public function testCountableInterface()
    {
        $response = $this->getFileBasedResponse('get_taxonomies_no_params.txt');

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->identicalTo($this->getTaxonomies))
            ->willReturn($response);

        $this->assertCount(3, $this->getTaxonomies);
    }
}
