<?php

namespace ArenaPl\ApiCall\Traits;

trait ProductVariantTrait
{
    /**
     * @var int
     */
    protected $productVariantId;

    /**
     * @param int $id
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric product variant ID provided
     */
    public function setProductVariantId($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('Non numeric product variant ID provided');
        }

        $this->productVariantId = (int) $id;

        return $this;
    }
}
