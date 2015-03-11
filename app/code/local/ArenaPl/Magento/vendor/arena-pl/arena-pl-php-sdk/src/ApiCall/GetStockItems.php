<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\PublicCountCurrentPageTrait;
use ArenaPl\ApiCall\Traits\PublicPageSortSearchApiCallTrait;
use ArenaPl\Exception\ApiCallException;

class GetStockItems extends AbstractStockLocationCall implements ApiCallInterface, \Countable, \IteratorAggregate
{
    use PublicCountCurrentPageTrait;
    use PublicPageSortSearchApiCallTrait;

    /**
     * {@inheritDoc}
     */
    protected static $metadataPackToLoad = MetadataHelper::METADATA_SIMPLE_PACK;

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_GET;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->buildPath('/stock_items');
    }

    /**
     * Returns stock items data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON('stock_items');
    }
}
