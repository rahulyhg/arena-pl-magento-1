<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\ProductDataTrait;
use ArenaPl\ApiCall\Traits\ProductNormalizersTrait;

class UpdateProduct extends AbstractProductCall implements ApiCallInterface
{
    use ProductNormalizersTrait;
    use ProductDataTrait;

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_PUT;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->buildPath();
    }

    /**
     * Updates product.
     *
     * @return array updated product data
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        $this->processProductData();

        return $this->makeCallJSON();
    }

    /**
     * Creates normalized product params to perform API call.
     */
    protected function processProductData()
    {
        $this->ensureProductNormalizersLoaded();

        $this->query = $this->getNormalizedProductData($this->productNormalizers);
    }
}
