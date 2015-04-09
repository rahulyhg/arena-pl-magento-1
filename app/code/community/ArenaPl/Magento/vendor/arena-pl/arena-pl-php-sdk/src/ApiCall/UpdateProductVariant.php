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
        $this->ensureProductNormalizersLoaded();

        $this->body = [];

        $implodedOptionValueIds = implode(',', $this->getOptionValueIds());
        if (!empty($implodedOptionValueIds)) {
            $this->body['variant']['option_value_ids'] = $implodedOptionValueIds;
        }

        foreach ($this->variantData as $key => $value) {
            $normalizedKey = trim(strtolower($key));
            $normalizedValue = isset($this->productNormalizers[$normalizedKey])
                ? call_user_func($this->productNormalizers[$normalizedKey], $value)
                : $value;
            $this->body['variant'][$normalizedKey] = $normalizedValue;
        }
    }
}
