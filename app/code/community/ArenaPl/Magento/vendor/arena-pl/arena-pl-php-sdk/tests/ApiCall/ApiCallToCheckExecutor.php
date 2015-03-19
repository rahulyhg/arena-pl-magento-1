<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCallExecutor\ApiCallExecutorInterface;

/**
 * Test class to check if {@see ApiCallExecutor} is passed through constructor to object.
 */
class ApiCallToCheckExecutor implements ApiCallInterface
{
    /**
     * @var ApiCallExecutorInterface
     */
    protected $apiCallExecutor;

    /**
     * @param ApiCallExecutorInterface $apiCallExecutor
     */
    public function __construct(ApiCallExecutorInterface $apiCallExecutor)
    {
        $this->apiCallExecutor = $apiCallExecutor;

        return $this;
    }

    /**
     * @return ApiCallExecutorInterface
     */
    public function getApiCallExecutor()
    {
        return $this->apiCallExecutor;
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function getBody()
    {
        throw new \BadFunctionCallException('Test object');
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function getMethod()
    {
        throw new \BadFunctionCallException('Test object');
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function getPath()
    {
        throw new \BadFunctionCallException('Test object');
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function getQuery()
    {
        throw new \BadFunctionCallException('Test object');
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function getResult()
    {
        throw new \BadFunctionCallException('Test object');
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function getMetadataFields()
    {
        throw new \BadFunctionCallException('Test object');
    }

    /**
     * @throws \BadFunctionCallException
     */
    public function getRawResponse()
    {
        throw new \BadFunctionCallException('Test object');
    }
}
