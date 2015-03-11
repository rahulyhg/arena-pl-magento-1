<?php

namespace ArenaPl\ApiCall\Traits;

use ArenaPl\ApiCall\ApiCallInterface;

trait PublicPageSortSearchApiCallTrait
{
    /**
     * {@inheritDoc}
     */
    public function setPage($page)
    {
        return parent::setPage($page);
    }

    /**
     * {@inheritDoc}
     */
    public function setResultsPerPage($resultsPerPage)
    {
        return parent::setResultsPerPage($resultsPerPage);
    }

    /**
     * {@inheritDoc}
     */
    public function setSort($field, $order)
    {
        return parent::setSort($field, $order);
    }

    /**
     * {@inheritDoc}
     */
    public function setSearch($field, $value, $method = ApiCallInterface::SEARCH_METHOD_CONTAINS)
    {
        return parent::setSearch($field, $value, $method);
    }

    /**
     * Returns an iterator to iterate over items (implements \IteratorAggregate).
     *
     * @return \ArrayIterator The iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getResult());
    }
}
