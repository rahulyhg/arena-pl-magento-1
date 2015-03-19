<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\ShipmentTrait;
use ArenaPl\Exception\ApiCallException;

class UpdateShipmentState extends AbstractApiCall implements ApiCallInterface
{
    use ShipmentTrait;

    const SHIPMENT_STATE_READY = 'ready';
    const SHIPMENT_STATE_SHIP = 'ship';

    /**
     * @var array
     */
    protected static $availableShipmentStates = [
        self::SHIPMENT_STATE_READY,
        self::SHIPMENT_STATE_SHIP,
    ];

    /**
     * @var string
     */
    protected $shipmentState;

    /**
     * @param string $state
     *
     * @return self
     *
     * @throws \InvalidArgumentException when invalid shipment state set
     */
    public function setShipmentState($state)
    {
        if (!in_array($state, self::$availableShipmentStates)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid shipment state "%s", use one of "%s"',
                $state,
                implode(', ', self::$availableShipmentStates)
            ));
        }

        $this->shipmentState = (string) $state;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException when shipment number or state is not set
     */
    public function getPath()
    {
        if (!$this->shipmentNumber) {
            throw new \RuntimeException('Shipment number not set');
        }

        if (!$this->shipmentState) {
            throw new \RuntimeException('Shipment state not set');
        }

        return sprintf(
            '/api/shipments/%s/%s',
            $this->shipmentNumber,
            $this->shipmentState
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
        return $this->makeCallJSON();
    }
}
