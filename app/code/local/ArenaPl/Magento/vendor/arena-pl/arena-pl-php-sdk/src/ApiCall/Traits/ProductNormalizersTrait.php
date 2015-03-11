<?php

namespace ArenaPl\ApiCall\Traits;

trait ProductNormalizersTrait
{
    /**
     * @var bool
     */
    protected $isPHPOld;

    /**
     * @var \Closure[]
     */
    protected $productNormalizers;

    /**
     * Old PHP considered as < 5.5.
     *
     * @return bool
     */
    protected function isPHPOld()
    {
        if ($this->isPHPOld === null) {
            $this->isPHPOld = PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION == 4;
        }

        return $this->isPHPOld;
    }

    /**
     * Loads normalizers to productNormalizers object field.
     */
    protected function ensureProductNormalizersLoaded()
    {
        if (!$this->productNormalizers) {
            $this->productNormalizers = $this->getProductNormalizersArray();
        }
    }

    /**
     * Returns [fieldName => closure] normalizers array.
     *
     * @return \Closure[]
     */
    protected function getProductNormalizersArray()
    {
        return [
            'price' => static function ($value) {
                if (is_numeric($value)) {
                    return number_format($value, 2, ',', '');
                }

                return $value;
            },
            'available_on' => function ($value) {
                if ($this->isPHPOld()) {
                    if ($value instanceof \DateTime) {
                        return $value->format(\DateTime::ATOM);
                    }
                } else {
                    if ($value instanceof \DateTimeInterface) {
                        return $value->format(\DateTime::ATOM);
                    }
                }

                return $value;
            },
            'taxon_ids' => static function ($value) {
                if (is_array($value)) {
                    return implode(',', $value);
                }

                return $value;
            },
        ];
    }
}
