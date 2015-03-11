<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\ShipmentTrait;
use ArenaPl\Exception\ApiCallException;

class UpdateShipmentTracking extends AbstractApiCall implements ApiCallInterface
{
    use ShipmentTrait;

    /**
     * @param scalar $tracking
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty tracking provided
     */
    public function setTracking($tracking)
    {
        if (empty($tracking)) {
            throw new \InvalidArgumentException('Empty tracking provided');
        }

        $this->body = [
            'shipment' => [
                'tracking' => $tracking,
            ],
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException when shipment number not set
     */
    public function getPath()
    {
        if (!$this->shipmentNumber) {
            throw new \RuntimeException('Shipment number not set');
        }

        return sprintf(
            '/api/shipments/%s',
            $this->shipmentNumber
        );
    }

    /**
     * Returns shipment data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return  $this->makeCallJSON();
    }
}
