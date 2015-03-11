<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\PublicCountCurrentPageTrait;
use ArenaPl\ApiCall\Traits\PublicPageSortSearchApiCallTrait;
use ArenaPl\Exception\ApiCallException;

class GetTaxonomies extends AbstractApiCall implements ApiCallInterface, \Countable, \IteratorAggregate
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
        return '/api/taxonomies';
    }

    /**
     * Returns taxonomies data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON('taxonomies');
    }
}
