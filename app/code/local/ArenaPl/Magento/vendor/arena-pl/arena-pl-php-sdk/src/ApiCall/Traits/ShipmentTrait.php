<?php

namespace ArenaPl\ApiCall\Traits;

use ArenaPl\ApiCall\ApiCallInterface;

trait ShipmentTrait
{
    /**
     * @var string
     */
    protected $shipmentNumber;

    /**
     * @param string $shipmentNumber
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty value provided
     */
    public function setShipmentNumber($shipmentNumber)
    {
        if (empty($shipmentNumber)) {
            throw new \InvalidArgumentException('Empty value provided');
        }

        $this->shipmentNumber = (string) $shipmentNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_PUT;
    }
}
