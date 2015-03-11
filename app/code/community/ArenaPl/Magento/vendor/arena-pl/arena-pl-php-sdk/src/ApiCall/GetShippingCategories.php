<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\PublicCountCurrentPageTrait;
use ArenaPl\ApiCall\Traits\PublicPageSortSearchApiCallTrait;
use ArenaPl\ApiCall\Traits\PublicPerPageTotalCountTrait;

class GetShippingCategories extends AbstractApiCall implements ApiCallInterface, \Countable, \IteratorAggregate
{
    use PublicCountCurrentPageTrait;
    use PublicPageSortSearchApiCallTrait;
    use PublicPerPageTotalCountTrait;

    /**
     * {@inheritDoc}
     */
    protected static $metadataPackToLoad = MetadataHelper::METADATA_FULL_PACK;

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
        return '/api/shipping_categories';
    }

    /**
     * Returns shipping categories data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON('shipping_categories');
    }
}
