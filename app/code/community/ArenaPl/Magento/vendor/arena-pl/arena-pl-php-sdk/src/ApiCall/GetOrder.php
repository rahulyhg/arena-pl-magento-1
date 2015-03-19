<?php

namespace ArenaPl\ApiCall;

use ArenaPl\Exception\ApiCallException;

class GetOrder extends AbstractOrderCall implements ApiCallInterface
{
    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_GET;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->buildPath();
    }

    /**
     * Returns order data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON();
    }
}
