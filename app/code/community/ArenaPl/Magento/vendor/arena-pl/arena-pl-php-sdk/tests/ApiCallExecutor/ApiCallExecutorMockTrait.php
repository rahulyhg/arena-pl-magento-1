<?php

namespace ArenaPl\Test\ApiCallExecutor;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

trait ApiCallExecutorMockTrait
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * Creates a new ApiCallExecutorInterface mock instance and assigns to $this->apiCallExecutor.
     */
    protected function setupClientMock()
    {
        $this->clientMock = $this->getMockBuilder('\ArenaPl\ApiCallExecutor\ApiCallExecutorInterface')
            ->getMock();
    }

    /**
     * Creates Guzzle response based on txt files in 'api_mocks' dir.
     *
     * @param string $mockFile
     *
     * @return Response
     */
    protected function getFileBasedResponse($mockFile)
    {
        return new Response(200, [],  Stream::factory(
            fopen(__DIR__ . '/api_mocks/' . $mockFile, 'r')
        ));
    }

    /**
     * Creates Guzzle response based on provided string.
     *
     * @param string $string
     *
     * @return Response
     */
    protected function getStringBasedResponse($string)
    {
        return new Response(200, [],  Stream::factory(
            $string
        ));
    }
}
