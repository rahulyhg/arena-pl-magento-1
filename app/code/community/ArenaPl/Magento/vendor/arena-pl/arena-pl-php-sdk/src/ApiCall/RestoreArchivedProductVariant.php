<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\ProductVariantTrait;

class RestoreArchivedProductVariant extends AbstractProductCall implements ApiCallInterface
{
    use ProductVariantTrait;

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_POST;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException when product variant ID is not set
     */
    public function getPath()
    {
        if (!$this->productVariantId) {
            throw new \RuntimeException('Product variant ID not set');
        }

        return $this->buildPath(sprintf(
            '/variants/%d/restore',
            $this->productVariantId
        ));
    }

    /**
     * Returns true if archived product variant is restored.
     *
     * @return bool
     */
    public function getResult()
    {
        return $this->makeCall(201);
    }
}
