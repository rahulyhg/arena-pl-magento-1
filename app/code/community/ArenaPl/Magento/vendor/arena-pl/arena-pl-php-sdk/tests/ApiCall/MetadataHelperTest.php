<?php

namespace ArenaPl\Test\ApiCall;

use ArenaPl\ApiCall\MetadataHelper;

class MetadataHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataHelper
     */
    protected $metadataHelper;

    protected function setUp()
    {
        $apiCallMock = $this->getMockBuilder('\ArenaPl\ApiCall\ApiCallInterface')->getMock();
        $apiCallMock->expects($this->once())->method('getMetadataFields')->willReturn([
           'existing' => true,
           'cast-to-int' => MetadataHelper::METADATA_CAST_TO_INT,
           'nullify-this' => 'not-important',
        ]);

        $this->metadataHelper = new MetadataHelper($apiCallMock);
    }

    public function testNonExistingDecodedFieldShouldBeSetToNull()
    {
        $this->metadataHelper->setMetadata([
            'existing' => 'yes',
            'cast-to-int' => '4',
        ]);

        $this->assertSame(
            'yes',
            $this->metadataHelper->getMetadata('existing')
        );
        $this->assertSame(
            4,
            $this->metadataHelper->getMetadata('cast-to-int')
        );
        $this->assertNull($this->metadataHelper->getMetadata('nullify-this'));
    }

    public function testNonExistingMetadataWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\RuntimeException', '/non-existing/');

        $this->metadataHelper->setMetadata([]);

        $this->metadataHelper->getMetadata('non-existing');
    }

    public function testWrongMetadataPackWillThrowException()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException', '/non-existing-pack/');

        $this->metadataHelper->setMetadata([]);

        MetadataHelper::getMetadataPack('non-existing-pack');
    }
}
