<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\TaxonTrait;

class GetTaxon extends AbstractApiCall implements ApiCallInterface
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
     * @throws \RuntimeException when taxon ID is not set
     */
    public function getPath()
    {
        if (!$this->taxonId) {
            throw new \RuntimeException('Taxon ID not set');
        }

        if (!$this->taxonChildId) {
            return sprintf('/api/taxonomies/%d', $this->taxonId);
        } else {
            return sprintf('/api/taxonomies/%d/taxons/%d', $this->taxonId, $this->taxonChildId);
        }
    }

    /**
     * Returns taxon data.
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
