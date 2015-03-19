<?php

namespace ArenaPl\Test\ApiCall\Traits;

use ArenaPl\ApiCall\Traits\ProductNormalizersTrait;

class ProductNormalizersTraitTest extends \PHPUnit_Framework_TestCase
{
    use ProductNormalizersTrait;

    protected function setUp()
    {
        $this->ensureProductNormalizersLoaded();
    }

    public function testPriceNormalization()
    {
        $this->assertSame('39,99', call_user_func($this->productNormalizers['price'], '39,99'));
        $this->assertSame('39,00', call_user_func($this->productNormalizers['price'], 39));
        $this->assertSame('39,57', call_user_func($this->productNormalizers['price'], 39.57));
    }

    public function testAvailableOnNormalization()
    {
        $now = new \DateTime();
        $this->assertSame(
            $now->format(\DateTime::ATOM),
            call_user_func($this->productNormalizers['available_on'], $now)
        );
        $this->assertSame(
            'some date',
            call_user_func($this->productNormalizers['available_on'], 'some date')
        );
    }

    public function testTaxonIdsConversion()
    {
        $this->assertSame('', call_user_func($this->productNormalizers['taxon_ids'], []));
        $this->assertSame('123', call_user_func($this->productNormalizers['taxon_ids'], [123]));
        $this->assertSame('123,456', call_user_func($this->productNormalizers['taxon_ids'], [123, 456]));
        $this->assertSame('123,456', call_user_func($this->productNormalizers['taxon_ids'], '123,456'));
    }

    public function testOldPhpDateInterface()
    {
        $this->isPHPOld = true;
        $normalizers = $this->getProductNormalizersArray();

        $now = new \DateTime();
        $this->assertSame(
            $now->format(\DateTime::ATOM),
            call_user_func($normalizers['available_on'], $now)
        );
        $this->assertSame(
            'another date',
            call_user_func($normalizers['available_on'], 'another date')
        );
    }
}
