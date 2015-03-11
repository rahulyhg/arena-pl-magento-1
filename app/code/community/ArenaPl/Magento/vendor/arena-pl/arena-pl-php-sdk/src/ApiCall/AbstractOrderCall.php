<?php

namespace ArenaPl\ApiCall;

abstract class AbstractOrderCall extends AbstractApiCall
{
    /**
     * @var string
     */
    protected $orderNumber;

    /**
     * Sets order number.
     *
     * @param string $orderNumber
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty order number provided
     */
    public function setOrderNumber($orderNumber)
    {
        if (empty($orderNumber)) {
            throw new \InvalidArgumentException('Empty order number provided');
        }

        $this->orderNumber = (string) $orderNumber;

        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return string
     *
     * @throws \RuntimeException when order number is not set
     */
    protected function buildPath($suffix = '')
    {
        if (!$this->orderNumber) {
            throw new \RuntimeException('Order number not set');
        }

        return sprintf(
            '/api/orders/%s%s',
            $this->orderNumber,
            $suffix
        );
    }
}
