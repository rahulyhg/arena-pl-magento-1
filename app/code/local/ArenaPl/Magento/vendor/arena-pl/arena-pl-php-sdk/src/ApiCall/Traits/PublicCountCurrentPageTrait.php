<?php

namespace ArenaPl\ApiCall\Traits;

trait PublicCountCurrentPageTrait
{
    /**
     * @return int
     *
     * @throws \RuntimeException when no call was performed
     */
    public function getCount()
    {
        return $this->metadataHelper->getMetadata('count');
    }

    /**
     * Implements \Countable.
     *
     * @return int The number of items
     */
    public function count()
    {
        return $this->getCount();
    }

    /**
     * @return int
     *
     * @throws \RuntimeException when no call was performed
     */
    public function getCurrentPage()
    {
        return $this->metadataHelper->getMetadata('current_page');
    }

    /**
     * @return int
     *
     * @throws \RuntimeException when no call was performed
     */
    public function getPages()
    {
        return $this->metadataHelper->getMetadata('pages');
    }
}
