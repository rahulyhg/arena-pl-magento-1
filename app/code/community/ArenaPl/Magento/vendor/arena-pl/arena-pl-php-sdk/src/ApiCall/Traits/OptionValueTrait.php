<?php

namespace ArenaPl\ApiCall\Traits;

trait OptionValueTrait
{
    /**
     * @var int[]
     */
    protected $optionValueIds = [];

    /**
     * @var array
     */
    protected $variantData = [];

    /**
     * @param int[] $optionValueIds
     *
     * @return self
     *
     * @throws \InvalidArgumentException when one or more values are not numeric
     */
    public function setOptionValueIds(array $optionValueIds)
    {
        $numericValues = array_filter($optionValueIds, 'is_numeric');
        if (count($numericValues) != count($optionValueIds)) {
            throw new \InvalidArgumentException('One or more values are not numeric');
        }

        $this->optionValueIds = array_map('intval', $optionValueIds);

        return $this;
    }

    /**
     * @param int $optionValueId
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric value provided
     */
    public function addOptionValueId($optionValueId)
    {
        if (!is_numeric($optionValueId)) {
            throw new \InvalidArgumentException('Non numeric value provided');
        }

        $this->optionValueIds[] = (int) $optionValueId;

        return $this;
    }

    /**
     * Returns unique option value IDs.
     *
     * @return int[]
     */
    protected function getOptionValueIds()
    {
        return array_unique($this->optionValueIds);
    }

    /**
     * Sets new set of variant data.
     *
     * Overwrites current variant data.
     *
     * @param array $variantData
     *
     * @return self
     */
    public function setVariantData(array $variantData)
    {
        $this->variantData = $variantData;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return self
     *
     * @throws \InvalidArgumentException When empty field name provided
     */
    public function setVariantField($name, $value)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Empty field name provided');
        }

        $this->variantData[$name] = $value;

        return $this;
    }
}
