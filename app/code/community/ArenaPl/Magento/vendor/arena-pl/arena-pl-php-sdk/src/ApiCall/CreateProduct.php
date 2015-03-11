<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\ProductDataTrait;
use ArenaPl\ApiCall\Traits\ProductNormalizersTrait;

class CreateProduct extends AbstractApiCall implements ApiCallInterface
{
    use ProductNormalizersTrait;
    use ProductDataTrait;

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
        return '/api/products';
    }

    /**
     * Creates product.
     *
     * @return array created product data
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
