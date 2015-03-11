<?php

namespace ArenaPl\ApiCall;

class RestoreArchivedProduct extends AbstractProductCall implements ApiCallInterface
{
    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_POST;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->buildPath('/restore');
    }

    /**
     * Returns true if archived product is restored.
     *
     * @return bool
     */
    public function getResult()
    {
        return $this->makeCall(201);
    }
}
