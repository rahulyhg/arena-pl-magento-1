<?php

class ArenaPl_Magento_Model_Mapper extends Mage_Core_Model_Abstract
{
    /**
     * EAV category attribute.
     */
    const ATTRIBUTE_CATEGORY_ARENA_TAXONOMY_ID = 'arena_taxonomy_id';

    /**
     * EAV category attribute.
     */
    const ATTRIBUTE_CATEGORY_ARENA_TAXON_ID = 'arena_taxon_id';

    const CACHE_KEY = 'arenapl_api_call';
    const CACHE_TIMEOUT = 3600;

    /**
     * @var ArenaPl_Magento_Helper_Data
     */
    protected $helper;

    /**
     * @var bool
     */
    protected $isDeveloperMode = false;

    /**
     * @var ArenaPl_Magento_Model_Resource_Mapper
     */
    protected $resource;

    protected function _construct()
    {
        $this->isDeveloperMode = Mage::getIsDeveloperMode();
        $this->resource = Mage::getResourceSingleton('arenapl_magento/mapper');
        $this->helper = Mage::helper('arenapl_magento');
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

        $rawApiCall = $this->resource->makeApiTaxonTreeCall(
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
     * @param array $returnData
     *
     * @return array
     */
    protected function parseTaxonTree($taxonomyId, array $rawApiCall, array &$returnData = [])
    {
        $taxonId = $rawApiCall['id'];
        $taxons = $rawApiCall['taxons'];
        unset($rawApiCall['id'], $rawApiCall['taxons']);

        $returnData[] = array_merge($rawApiCall, [
            'taxon_id' => $taxonId,
            'taxonomy_id' => $taxonomyId,
        ]);

        foreach ($taxons as $taxon) {
            $this->parseTaxonTree($taxonomyId, $taxon, $returnData);
        }

        return $returnData;
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

                $taxonomiesData = $this->resource->getTaxonomies();
                if (is_array($taxonomiesData)) {
                    foreach ($taxonomiesData as $row) {
                        $returnData[] = $this->processRawTaxonData($row['root']);
                    }
                }

                return $returnData;
            },
            [self::CACHE_KEY],
            self::CACHE_TIMEOUT
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
                return $this->resource->getTaxon($taxonomyId, $taxonId);
            },
            [self::CACHE_KEY],
            self::CACHE_TIMEOUT
        );
    }

    /**
     * @param array $taxonsData
     *
     * @return bool
     */
    public function saveCategoryMappings(array $taxonsData)
    {
        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_PRE_SAVE_MAPPED_CATEGORIES,
            [
                'taxons_data' => $taxonsData,
            ]
        );

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

        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_POST_SAVE_MAPPED_CATEGORIES,
            [
                'saved_categories' => $collection,
            ]
        );

        return true;
    }

    /**
     * @return array|null
     *
     * @throws \RuntimeException when multiple stock locations found
     */
    public function getArenaStockLocation()
    {
        $stockLocations = $this->resource->getStockLocations();
        if (empty($stockLocations)) {
            return;
        }

        if (count($stockLocations) > 1) {
            throw new \RuntimeException(sprintf(
                'Found %d stock locations, current module version doesnt support many stock locations',
                count($stockLocations)
            ));
        }

        return current($stockLocations);
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array
     */
    public function getCategoryAttributes($taxonomyId, $taxonId)
    {
        $optionTypes = [];
        $properties = [];

        $taxonData = $this->resource->getTaxon($taxonomyId, $taxonId);
        if (is_array($taxonData) && !empty($taxonData['prototype'])) {
            $optionTypes = empty($taxonData['prototype']['spree_option_types']) ? [] : $taxonData['prototype']['spree_option_types'];
            $properties =  empty($taxonData['prototype']['spree_properties']) ? [] : $taxonData['prototype']['spree_properties'];
        }

        return [
            'option_types' => $optionTypes,
            'properties' => $properties,
        ];
    }

    /**
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return int
     */
    public function getTotalCategoryAttributesNum($taxonomyId, $taxonId)
    {
        $categoryAttributes = $this->getCategoryAttributes(
            (int) $taxonomyId,
            (int) $taxonId
        );

        return count($categoryAttributes['option_types']) + count($categoryAttributes['properties']);
    }
}
