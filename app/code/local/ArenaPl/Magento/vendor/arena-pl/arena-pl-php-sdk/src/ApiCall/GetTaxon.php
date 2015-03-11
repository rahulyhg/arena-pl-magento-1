<?php

namespace ArenaPl\ApiCall;

class GetTaxon extends AbstractApiCall implements ApiCallInterface
{
    /**
     * @var int
     */
    protected $taxonId;

    /**
     * @var int|null
     */
    protected $taxonChildId = null;

    /**
     * @param int $taxonId
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric taxon ID provided
     */
    public function setTaxonId($taxonId)
    {
        if (!is_numeric($taxonId)) {
            throw new \InvalidArgumentException('Non numeric taxon ID provided');
        }

        $this->taxonId = (int) $taxonId;

        return $this;
    }

    /**
     * @param int $taxonChildId
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric taxonChildId ID provided
     */
    public function setTaxonChildId($taxonChildId)
    {
        if (!is_numeric($taxonChildId)) {
            throw new \InvalidArgumentException('Non numeric taxon child ID provided');
        }

        $this->taxonChildId = (int) $taxonChildId;

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
