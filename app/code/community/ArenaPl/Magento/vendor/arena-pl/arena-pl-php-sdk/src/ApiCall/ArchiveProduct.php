<?php

namespace ArenaPl\ApiCall;

class ArchiveProduct extends AbstractProductCall implements ApiCallInterface
{
    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_DELETE;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->buildPath();
    }

    /**
     * Returns true if product archived.
     *
     * @return bool
     */
    public function getResult()
    {
        return $this->makeCall(204);
    }
}
