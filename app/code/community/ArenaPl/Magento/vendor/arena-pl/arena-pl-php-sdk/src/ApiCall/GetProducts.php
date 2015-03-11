<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\PublicCountCurrentPageTrait;
use ArenaPl\ApiCall\Traits\PublicPageSortSearchApiCallTrait;
use ArenaPl\ApiCall\Traits\PublicPerPageTotalCountTrait;
use ArenaPl\Exception\ApiCallException;

class GetProducts extends AbstractApiCall implements ApiCallInterface, \Countable, \IteratorAggregate
{
    use PublicCountCurrentPageTrait;
    use PublicPerPageTotalCountTrait;
    use PublicPageSortSearchApiCallTrait;

    /**
     * {@inheritDoc}
     */
    protected static $metadataPackToLoad = MetadataHelper::METADATA_FULL_PACK;

    /**
     * True shows archived products.
     *
     * @param bool $showDeleted
     *
     * @return self
     */
    public function setShowDeleted($showDeleted)
    {
        $this->query['show_deleted'] = (bool) $showDeleted;

        return $this;
    }

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
        return '/api/products';
    }

    /**
     * Returns products data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON('products');
    }
}
