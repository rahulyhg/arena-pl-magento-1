<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\StockItemTrait;
use ArenaPl\Exception\ApiCallException;

class GetStockItem extends AbstractStockLocationCall implements ApiCallInterface
{
    use StockItemTrait;

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_GET;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        if (!$this->stockItemId) {
            throw new \RuntimeException('Stock item ID not set');
        }

        return $this->buildPath(sprintf(
            '/stock_items/%d',
            $this->stockItemId
        ));
    }

    /**
     * Returns stock item data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON();
    }
}
