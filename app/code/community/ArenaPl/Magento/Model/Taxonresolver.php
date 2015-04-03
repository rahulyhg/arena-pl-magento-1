<?php

class ArenaPl_Magento_Model_Taxonresolver
{
    const CACHE_KEY = 'arenapl_taxon_resolver';
    const CACHE_TIMEOUT = 10000;

    /**
     * @var ArenaPl_Magento_Helper_Data
     */
    protected $helper;

    /**
     * @var ArenaPl_Magento_Model_Resource_Mapper
     */
    protected $resource;

    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    /**
     * @param ArenaPl_Magento_Model_Mapper $mapper
     */
    public function __construct(ArenaPl_Magento_Model_Mapper $mapper)
    {
        $this->mapper = $mapper;
        $this->resource = Mage::getResourceSingleton('arenapl_magento/mapper');
        $this->helper = Mage::helper('arenapl_magento');
    }

    /**
     * @param string $permalink
     *
     * @return array|null
     */
    public function getTaxonDataFromPermalink($permalink)
    {
        return $this->helper->cacheExpensiveCall(
            "arenapl_taxonresolver_$permalink",
            function () use ($permalink) {
                return $this->getTaxonDataFromPermalinkInnerFunction($permalink);
            },
            [self::CACHE_KEY],
            self::CACHE_TIMEOUT
        );
    }

    /**
     * @param string $permalink
     *
     * @return array|null
     */
    protected function getTaxonDataFromPermalinkInnerFunction($permalink)
    {
        if (empty($permalink)) {
            return;
        }

        $taxonomiesData = $this->resource->getTaxonomies();
        if (!is_array($taxonomiesData)) {
            return;
        }

        $exploded = explode('/', $permalink, 2);
        $taxonomyPermalink = $exploded[0];
        $taxonomyId = null;
        $taxonId = null;

        foreach ($taxonomiesData as $taxonomy) {
            if ($taxonomy['root']['permalink'] === $taxonomyPermalink) {
                $taxonomyId = (int) $taxonomy['root']['taxonomy_id'];
                $taxonId = (int) $taxonomy['root']['id'];
                break;
            }
        }

        if (!$taxonomyId || !$taxonId) {
            return;
        }

        $taxonTree = $this->mapper->getTaxonTree([
            'taxonomy_id' => $taxonomyId,
            'taxon_id' => $taxonId,
        ]);

        if (empty($taxonTree)) {
            return;
        }

        foreach ($taxonTree as $row) {
            if ($row['permalink'] == $permalink) {
                $taxonData = $this->mapper->getTaxonData($row['taxonomy_id'], $row['taxon_id']);

                return $taxonData;
            }
        }
    }

    /**
     * @param array $taxonData
     *
     * @return array
     *
     * @throws \RuntimeException When parent taxon data cannot be fetched
     */
    public function getBaseTaxon(array $taxonData)
    {
        $filteredBaseTaxons = [];
        foreach ($this->mapper->getBaseTaxons() as $baseTaxon) {
            if ($taxonData['taxonomy_id'] == $baseTaxon['taxonomy_id']) {
                $taxonKey = sprintf('%d/%d', $baseTaxon['taxonomy_id'], $baseTaxon['taxon_id']);
                $filteredBaseTaxons[$taxonKey] = $baseTaxon;
            }
        }

        $taxonKey = sprintf('%d/%d', $taxonData['taxonomy_id'], $taxonData['taxon_id']);
        if (isset($filteredBaseTaxons[$taxonKey])) {
            return $filteredBaseTaxons[$taxonKey];
        }

        $parentId = $taxonData['parent_id'];
        while (!empty($parentId)) {
            $taxonKey = sprintf('%d/%d', $taxonData['taxonomy_id'], $parentId);
            if (isset($filteredBaseTaxons[$taxonKey])) {
                return $filteredBaseTaxons[$taxonKey];
            }

            $parentTaxonData = $this->resource->makeApiTaxonCall($taxonData['taxonomy_id'], $parentId);
            if (!is_array($parentTaxonData)) {
                throw new \RuntimeException(sprintf(
                    'Cant fetch taxon data "%d/%d"',
                    $taxonData['taxonomy_id'],
                    $parentId
                ));
            }
            $taxonData = $this->resource->processRawTaxonData($parentTaxonData);
            $parentId = $taxonData['parent_id'];
        }

        return $taxonData;
    }

    /**
     * @return array
     */
    public function getBaseTaxons()
    {
        $taxonomiesData = $this->resource->getTaxonomies();
        if (!is_array($taxonomiesData)) {
            return [];
        }

        $taxonomiesDataCount = count($taxonomiesData);

        if ($taxonomiesDataCount == 0) {
            return [];
        } elseif ($taxonomiesDataCount == 1) {
            $currentTaxonomies = current($taxonomiesData);

            return $this->processBaseTaxons($currentTaxonomies['root']['taxons']);
        } else {
            foreach ($taxonomiesData as $row) {
                if (!empty($row['default']) && $row['default']) {
                    return $this->processBaseTaxons($row['root']['taxons']);
                }
            }

            $returnData = [];
            foreach ($taxonomiesData as $row) {
                $returnData[] = $this->resource->processRawTaxonData($row['root']);
            }

            return $returnData;
        }
    }

    /**
     * @param array $taxons
     *
     * @return array
     */
    protected function processBaseTaxons(array $taxons)
    {
        $returnData = [];

        foreach ($taxons as $row) {
            $returnData[] = $this->resource->processRawTaxonData($row);
        }

        return $returnData;
    }
}
