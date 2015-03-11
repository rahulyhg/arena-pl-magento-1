<?php

namespace ArenaPl\ApiCall;

class MetadataHelper
{
    /**
     * Metadata value should be cast to integer.
     */
    const METADATA_CAST_TO_INT = 'int';

    /**
     * Metadata containing following info:
     * - count
     * - total_count
     * - current_page
     * - per_page
     * - pages.
     */
    const METADATA_FULL_PACK = 'full_pack';

    /**
     * Metadata containing following info:
     * - count
     * - current_page
     * - pages.
     */
    const METADATA_SIMPLE_PACK = 'simple_pack';

    /**
     * Metadata fields to load according to pack names.
     *
     * @var array
     */
    protected static $metadataPacks = [
        self::METADATA_FULL_PACK => [
            'count' => self::METADATA_CAST_TO_INT,
            'total_count' => self::METADATA_CAST_TO_INT,
            'current_page' => self::METADATA_CAST_TO_INT,
            'per_page' => self::METADATA_CAST_TO_INT,
            'pages' => self::METADATA_CAST_TO_INT,
        ],
        self::METADATA_SIMPLE_PACK => [
            'count' => self::METADATA_CAST_TO_INT,
            'current_page' => self::METADATA_CAST_TO_INT,
            'pages' => self::METADATA_CAST_TO_INT,
        ],
    ];

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var ApiCallInterface
     */
    protected $apiCall;

    /**
     * @param ApiCallInterface $apiCall
     *
     * @return self
     */
    public function __construct(ApiCallInterface $apiCall)
    {
        $this->apiCall = $apiCall;

        return $this;
    }

    /**
     * @param string $packName
     *
     * @return string[]
     *
     * @throws \InvalidArgumentException when metadata pack is not recognized
     */
    public static function getMetadataPack($packName)
    {
        if (!isset(self::$metadataPacks[$packName])) {
            throw new \InvalidArgumentException(sprintf(
                'Metadata pack "%s" not recognized, use one of "%s"',
                $packName,
                implode(', ', array_keys(self::$metadataPacks))
            ));
        }

        return self::$metadataPacks[$packName];
    }

    /**
     * Resets current metadata and sets new values.
     *
     * @param array $decoded
     */
    public function setMetadata(array $decoded)
    {
        $this->metadata = [];
        foreach ($this->apiCall->getMetadataFields() as $fieldName => $castTo) {
            if (!array_key_exists($fieldName, $decoded)) {
                $this->metadata[$fieldName] = null;
                continue;
            }

            if (self::METADATA_CAST_TO_INT === $castTo) {
                $this->metadata[$fieldName] = (int) $decoded[$fieldName];
            } else {
                $this->metadata[$fieldName] = $decoded[$fieldName];
            }
        }
    }

    /**
     * Returns metadata value.
     *
     * @param string $metadata
     *
     * @return string|int
     *
     * @throws \RuntimeException when requested metadata is not set
     */
    public function getMetadata($metadata)
    {
        if (empty($this->metadata)) {
            $this->apiCall->getResult();
        }

        if (!array_key_exists($metadata, $this->metadata)) {
            throw new \RuntimeException(sprintf(
                'Metadata "%s" not set', $metadata
            ));
        }

        return $this->metadata[$metadata];
    }
}
