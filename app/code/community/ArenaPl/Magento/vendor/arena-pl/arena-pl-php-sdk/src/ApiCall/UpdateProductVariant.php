<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\OptionValueTrait;
use ArenaPl\ApiCall\Traits\ProductNormalizersTrait;
use ArenaPl\ApiCall\Traits\ProductVariantTrait;
use ArenaPl\Exception\ApiCallException;

class UpdateProductVariant extends AbstractProductCall implements ApiCallInterface
{
    use ProductVariantTrait;
    use ProductNormalizersTrait;
    use OptionValueTrait;

    /**
     * Normalized price.
     *
     * @var string|null
     */
    protected $price = null;

    /**
     * @param mixed $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->ensureProductNormalizersLoaded();

        $this->price = call_user_func(
            $this->productNormalizers['price'],
            $price
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_PUT;
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
     * Returns modified product variant data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        $this->processVariantData();

        return $this->makeCallJSON();
    }

    /**
     * Creates normalized variant params to perform API call.
     */
    protected function processVariantData()
    {
        $this->body = [];

        $implodedOptionValueIds = implode(',', $this->getOptionValueIds());
        if (!empty($implodedOptionValueIds)) {
            $this->body['variant']['option_value_ids'] = $implodedOptionValueIds;
        }

        if ($this->price !== null) {
            $this->body['variant']['price'] = $this->price;
        }
    }
}
