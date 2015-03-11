<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\ApiCall\UpdateStockItem;
use ArenaPl\Test\ApiCallExecutor\ApiCallExecutorMockTrait;

class UpdateStockItemTest extends \PHPUnit_Framework_TestCase
{
    use ApiCallExecutorMockTrait;

    /**
     * @var UpdateStockItem
     */
    protected $updateStockItem;

    protected function setUp()
    {
        $this->setupClientMock();

        $this->updateStockItem = new UpdateStockItem($this->clientMock);
    }

    public function testPathBuild()
    {
        $this->updateStockItem->setStockLocationId(123);
        $this->updateStockItem->setStockItemId(456);

        $this->assertSame(
            '/api/stock_locations/123/stock_items/456',
            $this->updateStockItem->getPath()
        );
    }

    public function testPathBuildWillThrowExceptionWhenStockItemIdNotSet()
    {
        $this->setExpectedException('\RuntimeException');

        $this->updateStockItem->setStockLocationId(123);

        $this->updateStockItem->getPath();
    }

    /**
     * @dataProvider countIncreaseProvider
     */
    public function testCountIncrease($count, $countOnHand, $force)
    {
        $this->updateStockItem->increaseCountOnHand($count);

        $this->checkCallbackExpectation([
            'stock_item' => [
                'count_on_hand' => $countOnHand,
                'force' => $force,
            ],
        ]);
    }

    public function countIncreaseProvider()
    {
        return [
            [2, 2, false],
            ['3', 3, false],
            [4.8, 4, false],
        ];
    }

    public function testCountIncreaseNonNumericWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/im not numeric/');

        $this->updateStockItem->increaseCountOnHand('im not numeric');
    }

    public function testCountIncreaseNegativeWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/-5/');

        $this->updateStockItem->increaseCountOnHand(-5);
    }

    /**
     * @dataProvider countDecreaseProvider
     */
    public function testCountDecrease($count, $countOnHand, $force)
    {
        $this->updateStockItem->decreaseCountOnHand($count);

        $this->checkCallbackExpectation([
            'stock_item' => [
                'count_on_hand' => $countOnHand,
                'force' => $force,
            ],
        ]);
    }

    public function countDecreaseProvider()
    {
        return [
            [2, -2, false],
            ['3', -3, false],
            [4.8, -4, false],
        ];
    }

    /**
     * @dataProvider countSetProvider
     */
    public function testSetCount($count, $countOnHand, $force)
    {
        $this->updateStockItem->setCountOnHand($count);

        $this->checkCallbackExpectation([
            'stock_item' => [
                'count_on_hand' => $countOnHand,
                'force' => $force,
            ],
        ]);
    }

    public function countSetProvider()
    {
        return [
            [-3, -3, true],
            ['3', 3, true],
            [4.8, 4, true],
        ];
    }

    /**
     * @param array $stockItemExpectation
     */
    public function checkCallbackExpectation(array $stockItemExpectation)
    {
        $callback = function (UpdateStockItem $closure) use ($stockItemExpectation) {
            $body = $closure->getBody();
            $this->assertSame($stockItemExpectation, $body);

            return $closure->getMethod() === ApiCallInterface::METHOD_PUT;
        };

        $this->clientMock
            ->expects($this->once())
            ->method('makeAPICall')
            ->with($this->callback($callback))
            ->willReturn($this->getStringBasedResponse(json_encode(['result' => 'ok'])));

        $this->assertSame([
            'result' => 'ok',
        ], $this->updateStockItem->getResult());
    }
}
