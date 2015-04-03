<?php

use ArenaPl\Client;

class ArenaPl_Magento_Model_Resource_Mapper
{
    const DB_TABLE_MAPPER_ATTRIBUTE = 'arenapl_mapper_attribute';
    const DB_TABLE_MAPPER_ATTRIBUTE_OPTION = 'arenapl_mapper_attribute_option';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ArenaPl_Magento_Helper_Data
     */
    protected $helper;

    public function __construct()
    {
        $this->helper = Mage::helper('arenapl_magento');
        $this->client = $this->helper->getClient();
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    public function processRawTaxonData(array $rawData)
    {
        return [
            'taxon_id' => $rawData['id'],
            'taxonomy_id' => $rawData['taxonomy_id'],
            'name' => $rawData['name'],
            'permalink' => $rawData['permalink'],
            'parent_id' => $rawData['parent_id'],
            'has_children' => !empty($rawData['taxons']),
            'children' => empty($rawData['taxons']) ? [] : $rawData['taxons'],
        ];
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array|null
     */
    public function makeApiTaxonCall($taxonomyId, $taxonId)
    {
        $cacheKey = sprintf(
            'arenapl_api_taxon_taxonomy_%d_taxon_%d',
            $taxonomyId,
            $taxonId
        );

        return $this->helper->cacheExpensiveCall(
            $cacheKey,
            function () use ($taxonomyId, $taxonId) {
                return $this->getTaxon($taxonomyId, $taxonId);
            },
            [ArenaPl_Magento_Model_Mapper::CACHE_KEY],
            ArenaPl_Magento_Model_Mapper::CACHE_TIMEOUT
        );
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array|null
     */
    public function makeApiTaxonTreeCall($taxonomyId, $taxonId)
    {
        try {
            return $this->client->getTaxonTree()
                ->setTaxonId((int) $taxonomyId)
                ->setTaxonChildId((int) $taxonId)
                ->getResult();
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array|null
     */
    public function getTaxon($taxonomyId, $taxonId)
    {
        try {
            return $this->client->getTaxon()
                ->setTaxonId((int) $taxonomyId)
                ->setTaxonChildId((int) $taxonId)
                ->getResult();
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param int $resultsPerPage
     *
     * @return array|null
     */
    public function getTaxonomies($resultsPerPage = 1000)
    {
        try {
            return $this->client->getTaxonomies()
                ->setResultsPerPage((int) $resultsPerPage)
                ->getResult();
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @return array|null
     */
    public function getStockLocations()
    {
        try {
            return $this->client->getStockLocations()
                ->setResultsPerPage(1000)
                ->getResult();
        } catch (\Exception $e) {
            return;
        }
    }
}
