<?php

namespace ArenaPl\Test\ApiCall\Traits;

use ArenaPl\ApiCall\Traits\OptionValueTrait;

class OptionValueTraitTest extends \PHPUnit_Framework_TestCase
{
    use OptionValueTrait;

    public function testOptionValueConversionCorrectness()
    {
        $this->assertSame([], $this->optionValueIds);

        $this->setOptionValueIds([1, '2', '3.3']);
        $this->assertSame([1, 2, 3], $this->optionValueIds);

        $this->addOptionValueId(4);
        $this->addOptionValueId('5');
        $this->assertSame([1, 2, 3, 4, 5], $this->optionValueIds);
    }

    public function testNonNumericValueInSetOptionValueIds()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->setOptionValueIds([1, 'im not numeric']);
    }

    public function testNonNumericValueInAddOptionValueId()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->addOptionValueId('im not numeric');
    }
}
