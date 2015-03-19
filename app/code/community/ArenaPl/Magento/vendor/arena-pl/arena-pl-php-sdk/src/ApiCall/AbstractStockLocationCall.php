<?php

namespace ArenaPl\ApiCall;

abstract class AbstractStockLocationCall extends AbstractApiCall
{
    /**
     * @var int
     */
    protected $stockLocationId;

    /**
     * Sets stock location ID.
     *
     * @param int $id
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric value provided
     */
    public function setStockLocationId($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('Non numeric value provided');
        }

        $this->stockLocationId = (int) $id;

        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return string
     *
     * @throws \RuntimeException when stock location ID is not set
     */
    protected function buildPath($suffix = '')
    {
        if (!$this->stockLocationId) {
            throw new \RuntimeException('Stock location ID not set');
        }

        return sprintf(
            '/api/stock_locations/%d%s',
            $this->stockLocationId,
            $suffix
        );
    }
}
