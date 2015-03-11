<?php

namespace ArenaPl\ApiCall;

class GetProduct extends AbstractProductCall implements ApiCallInterface
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
     * Returns product data.
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
