<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\OptionValueTrait;
use ArenaPl\ApiCall\Traits\ProductNormalizersTrait;

class CreateProductVariant extends AbstractProductCall implements ApiCallInterface
{
    use ProductNormalizersTrait;
    use OptionValueTrait;

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
        return $this->buildPath('/variants');
    }

    /**
     * Creates product variant.
     *
     * @return array created product variant data
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

        $this->query = [];

        $implodedOptionValueIds = implode(',', $this->getOptionValueIds());
        if (!empty($implodedOptionValueIds)) {
            $this->query['variant[option_value_ids]'] = $implodedOptionValueIds;
        }

        foreach ($this->variantData as $key => $value) {
            $normalizedKey = trim(strtolower($key));
            $normalizedValue = isset($this->productNormalizers[$normalizedKey])
                ? call_user_func($this->productNormalizers[$normalizedKey], $value)
                : $value;
            $this->query['variant[' . $normalizedKey . ']'] = $normalizedValue;
        }
    }
}
