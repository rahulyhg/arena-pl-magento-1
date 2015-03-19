<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\StockItemTrait;
use ArenaPl\Exception\ApiCallException;

class UpdateStockItem extends AbstractStockLocationCall implements ApiCallInterface
{
    use StockItemTrait;

    /**
     * @var array
     */
    protected $stockItemData = [];

    /**
     * Sets new set of stock item data.
     *
     * Overwrites current stock item data.
     *
     * @param array $stockItemData
     *
     * @return self
     */
    public function setStockItemData(array $stockItemData)
    {
        $this->stockItemData = $stockItemData;

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
    public function setStockItemField($name, $value)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Empty field name provided');
        }

        $this->stockItemData[$name] = $value;

        return $this;
    }

    /**
     * @param int $increaseBy
     *
     * @return self
     */
    public function increaseCountOnHand($increaseBy)
    {
        $this->setCountOnHandInternal($increaseBy, false, false);

        return $this;
    }

    /**
     * @param int $decreaseBy
     *
     * @return self
     */
    public function decreaseCountOnHand($decreaseBy)
    {
        $this->setCountOnHandInternal($decreaseBy, false, true);

        return $this;
    }

    /**
     * @param int $count
     *
     * @return self
     */
    public function setCountOnHand($count)
    {
        $this->setCountOnHandInternal($count, true, false);

        return $this;
    }

    /**
     * @param int  $count
     * @param bool $force
     * @param bool $negate
     *
     * @throws \InvalidArgumentException when non numeric value provided or negative number provided where positive needed
     */
    protected function setCountOnHandInternal($count, $force, $negate)
    {
        if (!is_numeric($count)) {
            throw new \InvalidArgumentException(sprintf(
                'Non numeric value provided, "%s" given',
                $count
            ));
        }

        $number = (int) $count;
        if (!$force && $number <= 0) {
            throw new \InvalidArgumentException(sprintf(
                'Expecting positive integer, "%s" given',
                $number
            ));
        }

        $this->setStockItemField('count_on_hand', $negate ? - $number : $number);
        $this->setStockItemField('force', (bool) $force);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_PUT;
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
     * Returns updated stock item data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        $this->processStockItemData();

        return $this->makeCallJSON();
    }

    /**
     * Creates normalized stock item body params to perform API call.
     */
    protected function processStockItemData()
    {
        $this->body = [
            'stock_item' => [],
        ];

        foreach ($this->stockItemData as $key => $value) {
            $normalizedKey = trim(strtolower($key));
            $this->body['stock_item'][$normalizedKey] = $value;
        }
    }
}
