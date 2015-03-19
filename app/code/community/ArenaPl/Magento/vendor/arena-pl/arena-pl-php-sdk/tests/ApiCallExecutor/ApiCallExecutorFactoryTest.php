<?php

namespace ArenaPl\Test\ApiCallExecutor;

use ArenaPl\ApiCallExecutor\ApiCallExecutorFactory;

class ApiCallExecutorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiCallExecutorFactory
     */
    protected $apiCallExecutorFactory;

    protected function setUp()
    {
        $this->apiCallExecutorFactory = new ApiCallExecutorFactory(
            'test.host',
            'test token',
            false
        );
    }

    public function testEndpointUrlProperlyBuilt()
    {
        $this->assertSame(
            'https://test.host.arena.pl/api',
            $this->apiCallExecutorFactory->getEndpointUrl()
        );

        $httpExecutor = new ApiCallExecutorFactory('test.host.http', 'test token', true);
        $this->assertSame(
            'http://test.host.http.arena.pl/api',
            $httpExecutor->getEndpointUrl()
        );
    }

    public function testGuzzleHandlerIsReturnedAsExecutor()
    {
        $this->assertInstanceOf(
            '\ArenaPl\ApiCallExecutor\GuzzleExecutor',
            $this->apiCallExecutorFactory->getApiCallExecutor()
        );
    }
}
