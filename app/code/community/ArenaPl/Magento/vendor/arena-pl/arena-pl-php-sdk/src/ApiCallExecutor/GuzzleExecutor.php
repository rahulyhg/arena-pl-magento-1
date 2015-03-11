<?php

namespace ArenaPl\ApiCallExecutor;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\Exception\ApiCallException;
use ArenaPl\Exception\NotFoundException;
use ArenaPl\Exception\SubdomainException;
use ArenaPl\Exception\UnauthorizedException;
use ArenaPl\Exception\UnprocessableEntityException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Stream\Stream;

class GuzzleExecutor implements ApiCallExecutorInterface
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var GuzzleClientInterface
     */
    protected $guzzleClient;

    /**
     * @param string $endpoint
     * @param string $token
     */
    public function __construct($endpoint, $token)
    {
        $this->endpoint = $endpoint;
        $this->token = $token;
    }

    /**
     * @return GuzzleClientInterface
     */
    public function getGuzzleClient()
    {
        $this->ensureGuzzleClientInitialized();

        return $this->guzzleClient;
    }

    /**
     * {@inheritdoc}
     */
    public function makeAPICall(ApiCallInterface $apiCall)
    {
        $this->ensureGuzzleClientInitialized();

        try {
            $request = $this->prepareRequest($apiCall);

            return $this->guzzleClient->send($request);
        } catch (RequestException $e) {
            throw $this->createProperAPICallException(
                $e,
                isset($request) ? $request : null
            );
        }
    }

    /**
     * @return GuzzleClientInterface
     */
    protected function createGuzzleClient()
    {
        return new GuzzleClient([
             'base_url' => $this->endpoint,
             'defaults' => [
                 'headers' => [
                     'X-Spree-Token' => $this->token,
                 ],
             ],
        ]);
    }

    /**
     * @param ApiCallInterface $apiCall
     *
     * @return RequestInterface
     *
     * @throws \InvalidArgumentException when unrecognized method
     */
    protected function prepareRequest(ApiCallInterface $apiCall)
    {
        static $methodWithBody = [
            ApiCallInterface::METHOD_POST,
            ApiCallInterface::METHOD_PUT,
        ];

        $method = $this->getRequestMethod($apiCall);

        $request = $this->guzzleClient->createRequest(
            $method,
            $apiCall->getPath()
        );

        $query = $apiCall->getQuery();
        if ($query) {
            $request->setQuery($query);
        }

        if (in_array($method, $methodWithBody, true)) {
            $body = $apiCall->getBody();
            if ($body) {
                $request->setBody(Stream::factory(json_encode($body)));
            }

            $currentHeaders = $request->getHeaders();
            $request->setHeaders(array_merge($currentHeaders, [
                'Content-Type' => 'application/json',
            ]));
        }

        return $request;
    }

    /**
     * @param ApiCallInterface $apiCall
     *
     * @return string
     *
     * @throws \InvalidArgumentException when unrecognized method
     */
    protected function getRequestMethod(ApiCallInterface $apiCall)
    {
        static $allowedMethods = [
            ApiCallInterface::METHOD_GET,
            ApiCallInterface::METHOD_POST,
            ApiCallInterface::METHOD_PUT,
            ApiCallInterface::METHOD_DELETE,
        ];

        $method = strtoupper($apiCall->getMethod());

        if (!in_array($method, $allowedMethods, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unrecognized method "%s", allowed one of "%s"',
                $method,
                implode(', ', $allowedMethods)
            ));
        }

        return $method;
    }

    /**
     * @param RequestException $exception
     * @param RequestInterface $request
     *
     * @return ApiCallException|RequestException
     */
    protected function createProperAPICallException(
        RequestException $exception,
        RequestInterface $request = null
    ) {
        $response = $exception->getResponse();
        $exceptionCode = $exception->getCode();

        if (401 === $exceptionCode) {
            return new UnauthorizedException($request, $response, 'Unauthorized');
        } elseif (404 === $exceptionCode) {
            return new NotFoundException($request, $response, 'Not Found');
        } elseif (422 === $exceptionCode) {
            return new UnprocessableEntityException($request, $response, 'Unprocessable Entity');
        } elseif (
            0 === $exceptionCode
            && stripos($exception->getMessage(), $request->getHost()) !== false
        ) {
            return new SubdomainException($request, $response, 'Wrong subdomain');
        } else {
            return $exception;
        }
    }

    protected function ensureGuzzleClientInitialized()
    {
        if (!$this->guzzleClient instanceof GuzzleClientInterface) {
            $this->guzzleClient = $this->createGuzzleClient();
        }
    }
}
