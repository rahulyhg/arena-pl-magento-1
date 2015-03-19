<?php

namespace ArenaPl\ApiCall\Traits;

trait TaxonTrait
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
}
