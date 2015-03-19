<?php

namespace ArenaPl\ApiCall\Traits;

trait StockItemTrait
{
    /**
     * @var int
     */
    protected $stockItemId;

    /**
     * @param int $id
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setStockItemId($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('Non numeric stock item ID provided');
        }

        $this->stockItemId = (int) $id;

        return $this;
    }
}
