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

    public function __construct()
    {
        $this->client = Mage::helper('arenapl_magento')->getClient();
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
