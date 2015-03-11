<?php

namespace ArenaPl\Test\ApiCall\Traits;

use ArenaPl\ApiCall\Traits\ProductVariantTrait;

class ProductVariantTraitTest extends \PHPUnit_Framework_TestCase
{
    use ProductVariantTrait;

    public function testNonNumericVariantWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/numeric/i');

        $this->setProductVariantId('im non numeric');
    }
}
