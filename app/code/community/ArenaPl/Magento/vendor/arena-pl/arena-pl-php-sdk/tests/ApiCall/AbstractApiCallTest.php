<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;
use ArenaPl\Test\ReflectionToolsTrait;

class AbstractApiCallTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;
    use ReflectionToolsTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractApiCallMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataHelperMock;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->abstractApiCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\AbstractApiCall')
            ->disableOriginalConstructor()
            ->setMethods(['initMetadataHelper'])
            ->getMock();

        $this->metadataHelperMock = $this->getMockBuilder('\ArenaPl\ApiCall\MetadataHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->abstractApiCallMock
            ->expects($this->once())
            ->method('initMetadataHelper')
            ->willReturn($this->metadataHelperMock);

        $this->abstractApiCallMock->__construct($this->clientMock);
    }

    public function testMalformedJsonContentsWillThrowException()
    {
        $this->setExpectedException('\ArenaPl\Exception\ApiCallException');

        $response = $this->getStringBasedResponse('malformed JSON');

        $this->metadataHelperMock->expects($this->never())->method('setMetadata');

        $this->invokeNonPublicObjectMethod(
            $this->abstractApiCallMock,
            'decodeJson',
            $response
        );
    }

    public function testDecodeJsonWithoutResponseBody()
    {
        $this->setExpectedExceptionRegExp('\ArenaPl\Exception\ApiCallException', '/no body/i');

        $response = $this->getStringBasedResponse('malformed JSON');
        $response->setBody(null);

        $this->invokeNonPublicObjectMethod(
            $this->abstractApiCallMock,
            'decodeJson',
            $response
        );
    }

    public function testDecodeJsonWillRewindResponseBody()
    {
        $response = $this->getStringBasedResponse(json_encode([
            'a' => 'a',
            'b' => 'b',
        ]));

        $response->getBody()->seek(2);

        $decoded = $this->invokeNonPublicObjectMethod(
            $this->abstractApiCallMock,
            'decodeJson',
            $response
        );

        $this->assertSame([
            'a' => 'a',
            'b' => 'b',
        ], $decoded);

        $this->assertSame(0, $response->getBody()->tell());
    }
}
