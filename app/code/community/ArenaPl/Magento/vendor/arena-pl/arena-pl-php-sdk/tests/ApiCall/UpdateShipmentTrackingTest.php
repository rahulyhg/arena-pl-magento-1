<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\UpdateShipmentTracking;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;
use ArenaPl\Test\ReflectionToolsTrait;

class UpdateShipmentTrackingTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;
    use ReflectionToolsTrait;

    /**
     * @var UpdateShipmentTracking
     */
    protected $updateShipmentTracking;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->updateShipmentTracking = new UpdateShipmentTracking($this->clientMock);
    }

    public function testSetProperTracking()
    {
        $this->assertSame([], $this->updateShipmentTracking->getBody());
        $this->assertSame([], $this->updateShipmentTracking->getQuery());

        $this->updateShipmentTracking->setTracking('track-me');

        $this->assertSame(
            [
                'shipment' => [
                    'tracking' => 'track-me',
                ],
            ],
            $this->updateShipmentTracking->getBody()
        );
        $this->assertSame([], $this->updateShipmentTracking->getQuery());
    }

    public function testEmptyTrackingWillThrowException()
    {
        $this->setExpectedExceptionRegExp(
            '\InvalidArgumentException',
            '/empty tracking/i'
        );

        $this->updateShipmentTracking->setTracking('');
    }

    public function testNotSetShipmentNumberWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/shipment number/i');

        $this->updateShipmentTracking->getPath();
    }

    public function testPathBuild()
    {
        $this->updateShipmentTracking->setShipmentNumber(12345);

        $this->assertSame(
            '/api/shipments/12345',
            $this->updateShipmentTracking->getPath()
        );
    }
}
