<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\UpdateShipmentState;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;
use ArenaPl\Test\ReflectionToolsTrait;

class UpdateShipmentStateTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;
    use ReflectionToolsTrait;

    /**
     * @var UpdateShipmentState
     */
    protected $updateShipmentState;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->updateShipmentState = new UpdateShipmentState($this->clientMock);
    }

    public function testProperShipmentState()
    {
        $this->updateShipmentState->setShipmentState(UpdateShipmentState::SHIPMENT_STATE_SHIP);

        $this->assertSame(
            UpdateShipmentState::SHIPMENT_STATE_SHIP,
            $this->getNonPublicObjectProperty(
                $this->updateShipmentState,
                'shipmentState'
            )
        );
    }

    public function testNonExistingShipmentStateWillThrowException()
    {
        $this->setExpectedExceptionRegExp(
            '\InvalidArgumentException',
            '/invalid shipment state/i'
        );

        $this->updateShipmentState->setShipmentState('non existing state');
    }

    public function testNotSetShipmentNumberWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/shipment number/i');

        $this->updateShipmentState->getPath();
    }

    public function testNotSetShipmentStateWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/shipment state/i');

        $this->updateShipmentState->setShipmentNumber(12345);

        $this->updateShipmentState->getPath();
    }

    public function testPathBuild()
    {
        $this->updateShipmentState->setShipmentNumber(12345);
        $this->updateShipmentState->setShipmentState(UpdateShipmentState::SHIPMENT_STATE_READY);

        $this->assertSame(
            '/api/shipments/12345/ready',
            $this->updateShipmentState->getPath()
        );
    }
}
