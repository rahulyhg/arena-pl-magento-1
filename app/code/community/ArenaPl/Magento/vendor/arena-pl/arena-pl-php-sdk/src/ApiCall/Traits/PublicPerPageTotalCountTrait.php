<?php

namespace ArenaPl\ApiCall\Traits;

trait PublicPerPageTotalCountTrait
{
    /**
     * @return int
     *
     * @throws \RuntimeException when no call was performed
     */
    public function getTotalCount()
    {
        return $this->metadataHelper->getMetadata('total_count');
    }

    /**
     * @return int
     *
     * @throws \RuntimeException when no call was performed
     */
    public function getPerPage()
    {
        return $this->metadataHelper->getMetadata('per_page');
    }
}
