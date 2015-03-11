<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\ProductVariantTrait;

class ArchiveProductVariant extends AbstractProductCall implements ApiCallInterface
{
    use ProductVariantTrait;

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_DELETE;
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
            '/variants/%d',
            $this->productVariantId
        ));
    }

    /**
     * Returns true if product variant is archived.
     *
     * @return bool
     */
    public function getResult()
    {
        return $this->makeCall(204);
    }
}
