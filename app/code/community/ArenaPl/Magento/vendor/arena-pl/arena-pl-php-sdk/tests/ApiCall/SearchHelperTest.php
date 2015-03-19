<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\SearchHelper;
use ArenaPl\Test\ReflectionToolsTrait;

class SearchHelperTest extends \PHPUnit_Framework_TestCase
{
    use ReflectionToolsTrait;

    /**
     * @var SearchHelper
     */
    protected $searchHelper;

    protected function setUp()
    {
        $this->searchHelper = new SearchHelper(new \stdClass());
        $this->setNonPublicObjectProperty(
            $this->searchHelper,
            'availableMethods',
            ['test-method']
        );
    }

    public function testInitWithNonObjectWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/not an object/i');

        new SearchHelper('im not object');
    }

    public function testEmptyFieldWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/empty/i');

        $this->searchHelper->setField('');
    }

    public function testKeyConstructionWithEmptyPropertiesWillThrowException()
    {
        $this->assertNull($this->getNonPublicObjectProperty($this->searchHelper, 'field'));
        $this->assertNull($this->getNonPublicObjectProperty($this->searchHelper, 'method'));

        $this->interceptRuntimeException($this->searchHelper);

        $this->searchHelper->setField('test-field');
        $this->assertSame('test-field', $this->getNonPublicObjectProperty($this->searchHelper, 'field'));

        $this->interceptRuntimeException($this->searchHelper);

        $this->setNonPublicObjectProperty($this->searchHelper, 'field', null);
        $this->assertNull($this->getNonPublicObjectProperty($this->searchHelper, 'field'));

        $this->searchHelper->setMethod('test-method');

        $this->interceptRuntimeException($this->searchHelper);
    }

    protected function interceptRuntimeException(SearchHelper $searchHelper)
    {
        try {
            $searchHelper->getKey();

            $this->fail('Key must not be constructed with empty field and/or method');
        } catch (\RuntimeException $e) {
            if (!preg_match('/empty/i', $e->getMessage())) {
                $this->fail(sprintf(
                    'Exception message "%s" should contain info about exception reason',
                    $e->getMessage()
                ));
            }
        }
    }

    public function testProperKeyConstruction()
    {
        $this->searchHelper->setField('test-field');
        $this->searchHelper->setMethod('test-method');

        $this->assertSame('q[test-field_test-method]', $this->searchHelper->getKey());
    }

    /**
     * @dataProvider valuesToAdjust
     */
    public function testPhpValuesShouldBeAdjustedToApi($value, $expectedResult)
    {
        $this->searchHelper->setValue($value);

        $this->assertSame($expectedResult, $this->searchHelper->getValue());
    }

    public function valuesToAdjust()
    {
        return [
            ['string', 'string'],
            ['345', '345'],
            [null, ''],
            [true, 1],
            [false, 0],
        ];
    }
}
