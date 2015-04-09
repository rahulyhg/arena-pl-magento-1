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

    public function testVariantDataSet()
    {
        $this->assertSame([], $this->variantData);

        $this->setVariantField('a', 1);
        $this->assertSame(['a' => 1], $this->variantData);

        $data = [
            'a' => 2,
            'b' => 'c',
        ];

        $this->setVariantData($data);
        $this->assertSame($data, $this->variantData);

        $this->setVariantField('a', 1);

        $data['a'] = 1;
        $this->assertSame($data, $this->variantData);
    }

    public function testEmptyVariantFieldWillThrowException()
    {
        $this->setExpectedExceptionRegExp(
            '\InvalidArgumentException',
            '/empty field/i'
        );

        $this->setVariantField('', 1);
    }
}
