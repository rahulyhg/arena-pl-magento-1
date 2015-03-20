<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\TaxonTrait;

class GetTaxonTree extends AbstractApiCall implements ApiCallInterface
{
    use TaxonTrait;

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_GET;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException when taxon ID or taxon child ID is not set
     */
    public function getPath()
    {
        if (!$this->taxonId) {
            throw new \RuntimeException('Taxon ID not set');
        }

        if (!$this->taxonChildId) {
            throw new \RuntimeException('Taxon child ID not set');
        }

        return sprintf(
            '/api/taxonomies/%d/taxons/%d/tree',
            $this->taxonId,
            $this->taxonChildId
        );
    }

    /**
     * Returns taxon tree data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON();
    }
}
