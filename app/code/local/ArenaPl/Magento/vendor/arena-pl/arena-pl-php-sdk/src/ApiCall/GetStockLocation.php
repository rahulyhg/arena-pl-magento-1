<?php

namespace ArenaPl\ApiCall;

use ArenaPl\Exception\ApiCallException;

class GetStockLocation extends AbstractStockLocationCall implements ApiCallInterface
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
     * Returns stock locations data.
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
