<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\OptionValueTrait;
use ArenaPl\ApiCall\Traits\ProductNormalizersTrait;

class CreateProductVariant extends AbstractProductCall implements ApiCallInterface
{
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
        $this->query = [];

        $implodedOptionValueIds = implode(',', $this->getOptionValueIds());
        if (!empty($implodedOptionValueIds)) {
            $this->query['variant[option_value_ids]'] = $implodedOptionValueIds;
        }

        if ($this->price !== null) {
            $this->query['variant[price]'] = $this->price;
        }
    }
}
