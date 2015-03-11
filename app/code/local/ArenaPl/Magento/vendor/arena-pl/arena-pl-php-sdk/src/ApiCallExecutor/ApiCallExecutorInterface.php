<?php

namespace ArenaPl\ApiCallExecutor;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\Exception\ApiCallException;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Every {@see ApiCallInterface} request has access to this interface only.
 */
interface ApiCallExecutorInterface
{
    /**
     * Executes given API call and returns response.
     *
     * @param ApiCallInterface $apiCall
     *
     * @return ResponseInterface
     *
     * @throws ApiCallException when unauthorized, not found, wrong subdomain provided
     */
    public function makeAPICall(ApiCallInterface $apiCall);
}
