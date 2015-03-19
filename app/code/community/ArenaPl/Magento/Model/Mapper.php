<?php

class ArenaPl_Magento_Model_Mapper extends Mage_Core_Model_Abstract
{
    const ATTRIBUTE_CATEGORY_ARENA_TAXONOMY_ID = 'arena_taxonomy_id';
    const ATTRIBUTE_CATEGORY_ARENA_TAXON_ID = 'arena_taxon_id';
    const ATTRIBUTE_PRODUCT_ARENA_ID = 'arena_product_id';

    /**
     * @var \ArenaPl\Client
     */
    protected $client;

    /**
     * @var ArenaPl_Magento_Helper_Data
     */
    protected $helper;

    /**
     * @var bool
     */
    protected $isDeveloperMode = false;

    protected function _construct()
    {
        $this->isDeveloperMode = Mage::getIsDeveloperMode();
        $this->helper = Mage::helper('arenapl_magento');
        $this->client = $this->helper->getClient();
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     *
     * @return bool
     */
    public function hasMappedTaxon(Mage_Catalog_Model_Category $category)
    {
        $taxonomyId = (int) $category->getArenaTaxonomyId();
        $taxonId = (int) $category->getArenaTaxonId();

        return $taxonomyId != 0 && $taxonId != 0;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     *
     * @return array|null
     */
    public function getMappedArenaTaxon(Mage_Catalog_Model_Category $category)
    {
        return $this->getTaxonData(
            $category->getArenaTaxonomyId(),
            $category->getArenaTaxonId()
        );
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array|null
     */
    public function getTaxonData($taxonomyId, $taxonId)
    {
        $data = $this->makeApiTaxonCall($taxonomyId, $taxonId);

        if (is_array($data)) {
            return $this->processRawTaxonData($data);
        }
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    protected function processRawTaxonData(array $rawData)
    {
        return [
            'taxon_id' => $rawData['id'],
            'taxonomy_id' => $rawData['taxonomy_id'],
            'name' => $rawData['name'],
            'parent_id' => $rawData['parent_id'],
            'has_children' => !empty($rawData['taxons']),
            'children' => $rawData['taxons'],
        ];
    }

    /**
     * @param array $taxonData
     *
     * @return array
     */
    public function getTaxonNameChain(array $taxonData)
    {
        $chain = [$taxonData['name']];

        $parentId = $taxonData['parent_id'];
        while (!empty($parentId)) {
            $data = $this->makeApiTaxonCall($taxonData['taxonomy_id'], $parentId);
            if (!is_array($data)) {
                break;
            }

            $parentId = $data['parent_id'];
            $chain[] = $data['name'];
        }

        return array_reverse($chain);
    }

    /**
     * @param array $taxonData
     *
     * @return array
     */
    public function getBaseTaxon(array $taxonData)
    {
        $parentId = $taxonData['parent_id'];
        while (!empty($parentId)) {
            $data = $this->makeApiTaxonCall($taxonData['taxonomy_id'], $parentId);
            if (!is_array($taxonData)) {
                break;
            }
            $taxonData = $this->processRawTaxonData($data);
            $parentId = $taxonData['parent_id'];
        }

        return $taxonData;
    }

    /**
     * @param array $baseTaxon
     *
     * @return array
     */
    public function getTaxonTree(array $baseTaxon)
    {
        $taxonomyId = (int) $baseTaxon['taxonomy_id'];

        $rawApiCall = $this->makeApiTaxonTreeCall(
            $taxonomyId,
            $baseTaxon['taxon_id']
        );

        if (is_array($rawApiCall)) {
            return $this->parseTaxonTree($taxonomyId, $rawApiCall);
        }

        return [];
    }

    /**
     * @param int   $taxonomyId
     * @param array $rawApiCall
     *
     * @return array
     */
    protected function parseTaxonTree($taxonomyId, array $rawApiCall)
    {
        static $sequence = ['taxon_id', 'name', 'pretty_name'];

        $returnArray = [];

        $currentStep = -1;
        $constructedTaxon = [
            'taxonomy_id' => $taxonomyId,
        ];

        array_walk_recursive($rawApiCall, function ($value) use (
            &$currentStep,
            &$constructedTaxon,
            &$returnArray,
            $taxonomyId,
            $sequence
        ) {
            if (++$currentStep > 2) {
                $currentStep = 0;
                $returnArray[] = $constructedTaxon;
                $constructedTaxon = [
                    'taxonomy_id' => $taxonomyId,
                ];
            }

            $constructedTaxon[$sequence[$currentStep]] = $value;
        });

        return $returnArray;
    }

    /**
     * @return array
     */
    public function getBaseTaxons()
    {
        return $this->helper->cacheExpensiveCall(
            'arenapl_api_base_taxons',
            function () {
                $returnData = [];

                try {
                    $result = $this->client->getTaxonomies()
                        ->setResultsPerPage(1000)
                        ->getResult();

                    foreach ($result as $row) {
                        $returnData[] = $this->processRawTaxonData($row['root']);
                    }
                } catch (\Exception $e) {
                }

                return $returnData;
            },
            ['arenapl_api_call'],
            3600
        );
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array|null
     */
    protected function makeApiTaxonCall($taxonomyId, $taxonId)
    {
        if ($this->isDeveloperMode) {
            Mage::log('API taxonomy_id ' . $taxonomyId . ' taxon_id ' . $taxonId, Zend_Log::DEBUG);
        }

        $cacheKey = sprintf(
            'arenapl_api_taxon_taxonomy_%d_taxon_%d',
            $taxonomyId,
            $taxonId
        );

        return $this->helper->cacheExpensiveCall(
            $cacheKey,
            function () use ($taxonomyId, $taxonId) {
                try {
                    $result = $this->client->getTaxon()
                        ->setTaxonId((int) $taxonomyId)
                        ->setTaxonChildId((int) $taxonId)
                        ->getResult();

                    return $result;
                } catch (\Exception $e) {
                }
            },
            ['arenapl_api_call'],
            3600
        );
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array|null
     */
    protected function makeApiTaxonTreeCall($taxonomyId, $taxonId)
    {
        try {
            $result = $this->client->getTaxonTree()
                ->setTaxonId((int) $taxonomyId)
                ->setTaxonChildId((int) $taxonId)
                ->getResult();

            return $result;
        } catch (\Exception $e) {
        }
    }

    /**
     * @param array $taxonsData
     *
     * @return bool
     */
    public function saveCategoryMappings(array $taxonsData)
    {
        /* @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category');

        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = $category->getCollection();
        $collection->addAttributeToFilter('entity_id', [
            'in' => array_keys($taxonsData),
        ]);

        /* @var $category Mage_Catalog_Model_Category */
        foreach ($collection as $category) {
            $entityId = $category->getEntityId();

            $category->setData(
                self::ATTRIBUTE_CATEGORY_ARENA_TAXONOMY_ID,
                (int) $taxonsData[$entityId]['taxonomy_id']
            );
            $category->setData(
                self::ATTRIBUTE_CATEGORY_ARENA_TAXON_ID,
                (int) $taxonsData[$entityId]['taxon_id']
            );

            $category->save();
        }

        return true;
    }
}
