<?php

namespace ArenaPl\Test\ApiCallExecutor;

use ArenaPl\Exception\NotFoundException;
use ArenaPl\Exception\SubdomainException;
use ArenaPl\Exception\UnauthorizedException;
use ArenaPl\ApiCallExecutor\GuzzleExecutor;
use GuzzleHttp\Event\EndEvent;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use ArenaPl\GuzzleHandler;

class GuzzleExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GuzzleExecutor
     */
    protected $guzzleHandler;

    protected function setUp()
    {
        $this->guzzleHandler = new GuzzleExecutor('http://test.host.arena.pl/api', 'test token');
    }

    public function testGuzzleClientIsProperlyConfigured()
    {
        $guzzleClient = $this->guzzleHandler->getGuzzleClient();

        $this->assertInstanceOf('\GuzzleHttp\ClientInterface', $guzzleClient);
        $this->assertSame('http://test.host.arena.pl/api', $guzzleClient->getBaseUrl());

        $headers = $guzzleClient->getDefaultOption('headers');
        $this->assertSame('test token', $headers['X-Spree-Token']);
    }

    public function testBadApiCalls()
    {
        $guzzleClient = $this->guzzleHandler->getGuzzleClient();

        $mock = new Mock([
            new Response(401),
            new Response(404),
            new Response(422),
            new RequestException(
                'error msg with "test.host.arena.pl"',
                $guzzleClient->createRequest('GET')
            ),
            new Response(4222),
        ]);

        $guzzleClient->getEmitter()->attach($mock);

        $apiCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\ApiCallInterface')->getMock();
        $apiCallMock->expects($this->exactly(5))->method('getMethod')->willReturn('GET');
        $apiCallMock->expects($this->exactly(5))->method('getPath')->willReturn('/api');
        $apiCallMock->expects($this->exactly(5))->method('getQuery')->willReturn([]);

        try {
            $this->guzzleHandler->makeAPICall($apiCallMock);
            $this->fail('Exception should be thrown');
        } catch (UnauthorizedException $ex) {
            $this->assertInstanceOf('\GuzzleHttp\Message\RequestInterface', $ex->getRequest());
        }

        try {
            $this->guzzleHandler->makeAPICall($apiCallMock);
            $this->fail('Exception should be thrown');
        } catch (NotFoundException $ex) {
            $this->assertInstanceOf('\GuzzleHttp\Message\RequestInterface', $ex->getRequest());
        }

        try {
            $this->guzzleHandler->makeAPICall($apiCallMock);
            $this->fail('Exception should be thrown');
        } catch (\ArenaPl\Exception\UnprocessableEntityException $ex) {
            $this->assertInstanceOf('\GuzzleHttp\Message\RequestInterface', $ex->getRequest());
        }

        try {
            $this->guzzleHandler->makeAPICall($apiCallMock);
            $this->fail('Exception should be thrown');
        } catch (SubdomainException $ex) {
            $this->assertInstanceOf('\GuzzleHttp\Message\RequestInterface', $ex->getRequest());
        }

        try {
            $this->guzzleHandler->makeAPICall($apiCallMock);
            $this->fail('Exception should be thrown');
        } catch (RequestException $ex) {
            $this->assertInstanceOf('\GuzzleHttp\Message\RequestInterface', $ex->getRequest());
        }
    }

    public function testGuzzleRequestIsProperlyConfiguredOnGetRequest()
    {
        $guzzleClient = $this->guzzleHandler->getGuzzleClient();

        $mock = new Mock([
            new Response(200, [], Stream::factory(json_encode([
                'test request' => 'is ok',
            ]))),
        ]);

        $emitter = $guzzleClient->getEmitter();
        $emitter->attach($mock);
        $emitter->on('end', function (EndEvent $event) {
            $request = $event->getRequest();
            $this->assertSame('GET', $request->getMethod());
            $this->assertNull($request->getBody());
        });

        $apiCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\ApiCallInterface')->getMock();
        $apiCallMock->expects($this->once())->method('getMethod')->willReturn('GET');
        $apiCallMock->expects($this->once())->method('getPath')->willReturn('/api/qwerty');
        $apiCallMock->expects($this->once())->method('getQuery')->willReturn([
            'query' => 'is',
            'always' => 'great',
        ]);

        $result = $this->guzzleHandler->makeAPICall($apiCallMock);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(
            'http://test.host.arena.pl/api/qwerty?query=is&always=great',
            $result->getEffectiveUrl()
        );

        $this->assertSame([
            'test request' => 'is ok',
        ], json_decode($result->getBody()->getContents(), true));
    }

    public function testGuzzleRequestIsProperlyConfiguredOnPostRequest()
    {
        $guzzleClient = $this->guzzleHandler->getGuzzleClient();

        $mock = new Mock([
            new Response(200, [],  Stream::factory(json_encode([
                'test request' => 'is ok',
            ]))),
        ]);

        $emitter = $guzzleClient->getEmitter();
        $emitter->attach($mock);
        $emitter->on('end', function (EndEvent $event) {
            $request = $event->getRequest();
            $this->assertSame('POST', $request->getMethod());
            $this->assertSame('', $request->getBody()->getContents());
        });

        $apiCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\ApiCallInterface')->getMock();
        $apiCallMock->expects($this->once())->method('getMethod')->willReturn('POST');
        $apiCallMock->expects($this->once())->method('getPath')->willReturn('/api/qwerty');
        $apiCallMock->expects($this->once())->method('getQuery')->willReturn([
            'query' => 'is',
            'always' => 'great',
        ]);
        $apiCallMock->expects($this->once())->method('getBody')->willReturn([
            'body' => 'is',
            'even' => 'better',
        ]);

        $result = $this->guzzleHandler->makeAPICall($apiCallMock);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(
            'http://test.host.arena.pl/api/qwerty?query=is&always=great',
            $result->getEffectiveUrl()
        );

        $this->assertSame([
            'test request' => 'is ok',
        ], json_decode($result->getBody()->getContents(), true));
    }

    public function testInvalidRequestMethodShouldThrowException()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $apiCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\ApiCallInterface')->getMock();
        $apiCallMock->expects($this->once())->method('getMethod')->willReturn('malformed method');

        $this->guzzleHandler->makeAPICall($apiCallMock);
    }
}
