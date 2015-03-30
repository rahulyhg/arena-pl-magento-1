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
     * @param int $taxonomyId
     * @param int $taxonId
     *
     * @return array|null
     */
    public function getTaxonPrototype($taxonomyId, $taxonId)
    {
        $data = $this->makeApiTaxonCall($taxonomyId, $taxonId);

        if (is_array($data)) {
            return $data['prototype'];
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

    /**
     * @param Mage_Catalog_Model_Category $category
     *
     * @return Mage_Catalog_Model_Resource_Eav_Attribute[]
     */
    public function getCategoryProductsAttributes(Mage_Catalog_Model_Category $category)
    {
        static $doNotDisplayAttributes = [
            ArenaPl_Magento_Model_Exportservice::ATTRIBUTE_PRODUCT_ARENA_ID,
        ];

        $returnAttributes = [];

        /* @var $categoryProducts Mage_Catalog_Model_Resource_Product_Collection */
        $categoryProducts = $category->getProductCollection();

        /* @var $product Mage_Catalog_Model_Product */
        foreach ($categoryProducts as $product) {
            $attributes = $product->getAttributes();

            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            foreach ($attributes as $attribute) {
                $isUserDefined = $attribute->getData('is_user_defined');
                if (!$isUserDefined
                    || in_array($attribute->getAttributeCode(), $doNotDisplayAttributes)
                ) {
                    continue;
                }

                $returnAttributes[$attribute->getAttributeId()] = $attribute;
            }
        }

        return $returnAttributes;
    }

    /**
     * @param int   $categoryId
     * @param array $attributesMapping
     * @param array $optionsMapping
     *
     * @return bool
     */
    public function saveCategoryAttributes(
        $categoryId,
        array $attributesMapping,
        array $optionsMapping
    ) {
        $category = ArenaPl_Magento_Helper_Data::getCategory((int) $categoryId);

        if (!$category instanceof Mage_Catalog_Model_Category) {
            return false;
        }

        if (!$this->hasMappedTaxon($category)) {
            return false;
        }

        $mappedTaxonData = $this->getMappedArenaTaxon($category);
        if (empty($mappedTaxonData)) {
            return false;
        }

        $readConnection =  ArenaPl_Magento_Helper_Data::getDBReadConnection();
        $writeConnection = ArenaPl_Magento_Helper_Data::getDBWriteConnection();

        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_PRE_SAVE_MAPPED_CATEGORY_ATTRIBUTES,
            [
                'category' => $category,
                'attributes_mapping' => $attributesMapping,
                'options_mapping' => $optionsMapping,
            ]
        );

        $writeConnection->beginTransaction();

        $this->deleteCurrentAttributeMapping($category, $readConnection, $writeConnection);

        $this->saveAttributesMapping($category, $mappedTaxonData, $attributesMapping, $writeConnection);
        $this->saveOptionsMapping($category, $mappedTaxonData, $optionsMapping, $readConnection, $writeConnection);

        $writeConnection->commit();

        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_POST_SAVE_MAPPED_CATEGORY_ATTRIBUTES,
            [
                'category' => $category,
                'attributes_mapping' => $attributesMapping,
                'options_mapping' => $optionsMapping,
            ]
        );

        return true;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param Varien_Db_Adapter_Interface $readConnection
     * @param Varien_Db_Adapter_Interface $writeConnection
     */
    protected function deleteCurrentAttributeMapping(
        Mage_Catalog_Model_Category $category,
        Varien_Db_Adapter_Interface $readConnection,
        Varien_Db_Adapter_Interface $writeConnection
    ) {
        $categoryAttributesIds = array_keys($this->getCategoryProductsAttributes($category));
        if (empty($categoryAttributesIds)) {
            return;
        }

        $writeConnection->delete(ArenaPl_Magento_Model_Resource_Mapper::DB_TABLE_MAPPER_ATTRIBUTE, [
            'attribute_id IN (?)' => $categoryAttributesIds,
        ]);

        $attributesOptionsIds = $this->getAttributesOptionsIds(
            $readConnection,
            $categoryAttributesIds
        );
        if (empty($attributesOptionsIds)) {
            return;
        }

        $writeConnection->delete(ArenaPl_Magento_Model_Resource_Mapper::DB_TABLE_MAPPER_ATTRIBUTE_OPTION, [
            'option_id IN (?)' => $attributesOptionsIds,
        ]);
    }

    /**
     * @param Varien_Db_Adapter_Interface $readConnection
     * @param int[]                       $categoryAttributesIds
     *
     * @return int[]
     */
    protected function getAttributesOptionsIds(
        Varien_Db_Adapter_Interface $readConnection,
        array $categoryAttributesIds
    ) {
        $result = $readConnection
            ->select()
            ->from('eav_attribute_option', 'option_id')
            ->distinct()
            ->where(
                'attribute_id IN (?)',
                array_unique($categoryAttributesIds),
                Zend_Db::PARAM_INT
            )
            ->query();

        $attributesOptionsIds = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $attributesOptionsIds[] = (int) $row['option_id'];
        }

        return $attributesOptionsIds;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param array                       $mappedTaxonData
     * @param array                       $attributesMapping
     * @param Varien_Db_Adapter_Interface $writeConnection
     */
    protected function saveAttributesMapping(
        Mage_Catalog_Model_Category $category,
        array $mappedTaxonData,
        array $attributesMapping,
        Varien_Db_Adapter_Interface $writeConnection
    ) {
        $filteredAttributesMapping = $this->getFilteredCategoryAttributes(
            $category,
            $mappedTaxonData,
            $attributesMapping
        );

        if (empty($filteredAttributesMapping)) {
            return;
        }

        $insertData = [];
        foreach ($filteredAttributesMapping as $attributeId => $arenaValue) {
            $insertData[] = [
                'attribute_id' => $attributeId,
                'arena_option_name' => $arenaValue,
            ];
        }

        $writeConnection->insertMultiple(
            ArenaPl_Magento_Model_Resource_Mapper::DB_TABLE_MAPPER_ATTRIBUTE,
            $insertData
        );
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param array                       $mappedTaxonData
     * @param array                       $attributesMapping
     *
     * @return array
     */
    protected function getFilteredCategoryAttributes(
        Mage_Catalog_Model_Category $category,
        array $mappedTaxonData,
        array $attributesMapping
    ) {
        $categoryAttributesIds = array_keys($this->getCategoryProductsAttributes($category));
        if (empty($categoryAttributesIds)) {
            return [];
        }

        $attributesMapping = array_intersect_key(
            $attributesMapping,
            array_flip($categoryAttributesIds)
        );

        $attributesMapping = array_map('trim', $attributesMapping);

        $arenaCategoryAttributes = $this->getCategoryAttributes(
            $mappedTaxonData['taxonomy_id'],
            $mappedTaxonData['taxon_id']
        );

        $validArenaCategoryAttributes = [];
        foreach ($arenaCategoryAttributes['option_types'] as $data) {
            $validArenaCategoryAttributes[] = $data['name'];
        }
        foreach ($arenaCategoryAttributes['properties'] as $data) {
            $validArenaCategoryAttributes[] = $data['name'];
        }

        $attributesMapping = array_filter($attributesMapping, function ($val) use ($validArenaCategoryAttributes) {
            if (empty($val)) {
                return false;
            }

            return in_array($val, $validArenaCategoryAttributes, true);
        });

        return $attributesMapping;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param array                       $mappedTaxonData
     * @param array                       $optionsMapping
     * @param Varien_Db_Adapter_Interface $readConnection
     * @param Varien_Db_Adapter_Interface $writeConnection
     */
    protected function saveOptionsMapping(
        Mage_Catalog_Model_Category $category,
        array $mappedTaxonData,
        array $optionsMapping,
        Varien_Db_Adapter_Interface $readConnection,
        Varien_Db_Adapter_Interface $writeConnection
    ) {
        $filteredOptionsMapping = $this->getFilteredCategoryOptions(
            $category,
            $mappedTaxonData,
            $optionsMapping,
            $readConnection
        );

        if (empty($filteredOptionsMapping)) {
            return;
        }

        $insertData = [];
        foreach ($filteredOptionsMapping as $optionId => $arenaValue) {
            $insertData[] = [
                'option_id' => $optionId,
                'arena_option_value_name' => $arenaValue,
            ];
        }

        $writeConnection->insertMultiple(
            ArenaPl_Magento_Model_Resource_Mapper::DB_TABLE_MAPPER_ATTRIBUTE_OPTION,
            $insertData
        );
    }

    protected function getFilteredCategoryOptions(
        Mage_Catalog_Model_Category $category,
        array $mappedTaxonData,
        array $optionsMapping,
        Varien_Db_Adapter_Interface $readConnection
    ) {
        $categoryAttributesIds = array_keys($this->getCategoryProductsAttributes($category));
        if (empty($categoryAttributesIds)) {
            return [];
        }

        $attributesOptionsIds = $this->getAttributesOptionsIds(
            $readConnection,
            $categoryAttributesIds
        );
        if (empty($attributesOptionsIds)) {
            return;
        }

        $optionsMapping = array_intersect_key(
            $optionsMapping,
            array_flip($attributesOptionsIds)
        );

        $optionsMapping = array_map('trim', $optionsMapping);

        $arenaCategoryAttributes = $this->getCategoryAttributes(
            $mappedTaxonData['taxonomy_id'],
            $mappedTaxonData['taxon_id']
        );

        $validArenaCategoryOptions = [];
        foreach ($arenaCategoryAttributes['option_types'] as $data) {
            foreach ($data['spree_option_values'] as $value) {
                $validArenaCategoryOptions[] = $value['name'];
            }
        }

        $optionsMapping = array_filter($optionsMapping, function ($val) use ($validArenaCategoryOptions) {
            if (empty($val)) {
                return false;
            }

            return in_array($val, $validArenaCategoryOptions, true);
        });

        return $optionsMapping;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getMappedProductAttributes(Mage_Catalog_Model_Product $product)
    {
        $readConnection = ArenaPl_Magento_Helper_Data::getDBReadConnection();

        $productAttributesIds = $this->getProductAttributeIds($product);
        $mappedAttributes = $this->getMappedAttributes($productAttributesIds, $readConnection);

        return $mappedAttributes;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int[]
     */
    protected function getProductAttributeIds(Mage_Catalog_Model_Product $product)
    {
        $productAttributesIds = [];

        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {
            $attributeId = $attribute->getAttributeId();
            if ($attributeId) {
                $productAttributesIds[] = (int) $attributeId;
            }
        }

        return $productAttributesIds;
    }

    /**
     * @param int[]                       $attributesIds
     * @param Varien_Db_Adapter_Interface $readConnection
     *
     * @return array
     */
    protected function getMappedAttributes(
        array $attributesIds,
        Varien_Db_Adapter_Interface $readConnection
    ) {
        $result = $readConnection
            ->select()
            ->from(ArenaPl_Magento_Model_Resource_Mapper::DB_TABLE_MAPPER_ATTRIBUTE)
            ->where(
                'attribute_id IN (?)',
                array_unique($attributesIds),
                Zend_Db::PARAM_INT
            )
            ->query();

        $mappedAattributes = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $mappedAattributes[$row['attribute_id']] = (string) $row['arena_option_name'];
        }

        return $mappedAattributes;
    }
}
