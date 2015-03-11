<?php

namespace ArenaPl\ApiCall\Traits;

trait ProductDataTrait
{
    /**
     * @var array
     */
    protected $productData = [];

    /**
     * Sets new set of product data.
     *
     * Overwrites current product data.
     *
     * @param array $productData
     *
     * @return self
     */
    public function setProductData(array $productData)
    {
        $this->productData = $productData;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty field name provided
     */
    public function setProductField($name, $value)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Empty field name provided');
        }

        $this->productData[$name] = $value;

        return $this;
    }

    /**
     * Returns normalized product keys and values.
     *
     * @param \Closure[] $normalizers
     *
     * @return array
     */
    protected function getNormalizedProductData(array $normalizers)
    {
        $normalizedProductData = [];

        foreach ($this->productData as $key => $value) {
            $normalizedKey = trim(strtolower($key));
            $normalizedValue = isset($normalizers[$normalizedKey])
                ? call_user_func($normalizers[$normalizedKey], $value)
                : $value;

            $normalizedProductData['product[' . $normalizedKey . ']'] = $normalizedValue;
        }

        return $normalizedProductData;
    }
}
