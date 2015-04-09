<?php

class ArenaPl_Magento_Model_Exportservice extends Mage_Core_Model_Abstract
{
    /**
     * EAV product attribute.
     */
    const ATTRIBUTE_PRODUCT_ARENA_ID = 'arena_product_id';

    /**
     * EAV product variant attribute.
     */
    const ATTRIBUTE_PRODUCT_VARIANT_ARENA_ID = 'arena_product_variant_id';

    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    /**
     * @var ArenaPl_Magento_Model_Resource_Exportservice
     */
    protected $resource;

    /**
     * @var ArenaPl_Magento_Model_Exportservicequery
     */
    protected $query;

    protected function _construct()
    {
        $this->resource = Mage::getResourceSingleton('arenapl_magento/exportservice');
        $this->mapper = Mage::getSingleton('arenapl_magento/mapper');
        $this->query = $this->initQuery();
    }

    /**
     * @return ArenaPl_Magento_Model_Exportservicequery
     */
    protected function initQuery()
    {
        return new ArenaPl_Magento_Model_Exportservicequery(
            $this->resource,
            $this->mapper
        );
    }
    
    /**
     * Warning! This method destroys arena product ID mapping.
     * 
     * @param Mage_Catalog_Model_Product $product
     */
    public function cleanArenaData(Mage_Catalog_Model_Product $product)
    {
        $product->setData(self::ATTRIBUTE_PRODUCT_ARENA_ID, null);
        $product->setData(self::ATTRIBUTE_PRODUCT_VARIANT_ARENA_ID, null);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function exportProduct(Mage_Catalog_Model_Product $product)
    {
        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_PRE_PRODUCT_EXPORT,
            [
                'product' => $product,
            ]
        );

        if ($this->query->isAnyProductCategoryMapped($product)) {
            $isMaster = $this->query->productShouldBeArenaMaster($product);

            if ($this->query->isProductOnStock($product)) {
                $this->exportProductOnStock($product, $isMaster);
            } else {
                $this->exportProductEmptyStock($product, $isMaster);
            }

            if ($isMaster) {
                $this->exportProductProperties($product);
                $this->exportProductOptionValues($product);
                if ($this->query->isProductColorVariant($product)) {
                    $this->setProductColorRelations($product);
                }
            }
        }

        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_POST_PRODUCT_EXPORT,
            [
                'product' => $product,
            ]
        );
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param bool                       $isMaster
     */
    protected function exportProductOnStock(Mage_Catalog_Model_Product $product, $isMaster)
    {
        $arenaProductId = null;
        $arenaProductVariantId = null;

        if ($this->query->isProductExported($product)) {
            $arenaProductId = $this->query->getArenaProductId($product);
            $arenaProductVariantId = $this->query->getArenaProductVariantId($product);

            if ($isMaster) {
                $this->resource->ensureArenaProductMasterVisible($arenaProductId);
                $this->resource->exportExistingMasterProduct(
                    $product,
                    $arenaProductId
                );
            } else {
                $this->resource->ensureArenaProductVariantVisible($arenaProductId, $arenaProductVariantId);
                $this->resource->exportExistingVariantProduct(
                    $product,
                    $arenaProductId,
                    $arenaProductVariantId
                );
            }
        } else {
            if ($isMaster) {
                list($arenaProductId, $arenaProductVariantId) = $this->resource->exportNewProduct($product);
            } else {
                $arenaProductId = $this->ensureMasterProductExported($product);
                $arenaProductVariantId = $this->resource->exportNewProductVariant(
                    $product,
                    $arenaProductId
                );
            }

            if ($arenaProductId && $arenaProductVariantId) {
                $this->saveArenaProductId($product, $arenaProductId, $arenaProductVariantId);
            }
        }

        if ($arenaProductId && $arenaProductVariantId) {
            $this->updateProductStockQuantity($product, $arenaProductId, $arenaProductVariantId);
            $this->exportImages($product, $arenaProductId, $arenaProductVariantId);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int Arena product ID
     *
     * @throws \RuntimeException When parent product cannot be fetched or exported
     */
    protected function ensureMasterProductExported(Mage_Catalog_Model_Product $product)
    {
        $parent = $this->query->getProductParent($product);

        if (!$parent instanceof Mage_Catalog_Model_Product) {
            throw new \RuntimeException(sprintf(
                'Cannot fetch parent product of "%d"',
                $product->getId()
            ));
        }

        if (!$this->query->isProductExported($parent)) {
            $this->exportProduct($parent);

            if (!$this->query->isProductExported($parent)) {
                throw new \RuntimeException(sprintf(
                    'Parent product "%d" cannot be exported',
                    $parent->getId()
                ));
            }
        }

        return $this->query->getArenaProductId($parent);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function exportProductEmptyStock(Mage_Catalog_Model_Product $product)
    {
        if ($this->query->isProductExported($product)) {
            $arenaProductId = $this->query->getArenaProductId($product);
            $arenaProductVariantId = $this->query->getArenaProductVariantId($product);
            $isMaster = $this->query->isProductArenaMaster($product);

            if ($isMaster) {
                $this->resource->ensureArenaProductMasterVisible($arenaProductId);
            } else {
                $this->resource->ensureArenaProductVariantVisible($arenaProductId, $arenaProductVariantId);
            }

            $this->updateProductStockQuantity($product, $arenaProductId, $arenaProductVariantId);
            $this->exportImages($product, $arenaProductId, $arenaProductVariantId);

            if ($isMaster) {
                $this->resource->archiveProduct($arenaProductId);
            } else {
                $this->resource->archiveProductVariant($arenaProductId, $arenaProductVariantId);
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     * @param int                        $arenaProductVariantId
     */
    protected function saveArenaProductId(
        Mage_Catalog_Model_Product $product,
        $arenaProductId,
        $arenaProductVariantId
    ) {
        $product->setData(self::ATTRIBUTE_PRODUCT_ARENA_ID, (int) $arenaProductId);
        $product->setData(self::ATTRIBUTE_PRODUCT_VARIANT_ARENA_ID, (int) $arenaProductVariantId);

        $product->save();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     * @param int                        $arenaProductVariantId
     */
    protected function exportImages(
        Mage_Catalog_Model_Product $product,
        $arenaProductId,
        $arenaProductVariantId
    ) {
        $isMaster = $this->query->isProductArenaMaster($product);

        if ($isMaster) {
            $this->resource->deleteExistingArenaMasterImages($arenaProductId);
        } else {
            $this->resource->deleteExistingArenaVariantImages($arenaProductId, $arenaProductVariantId);
        }

        $productImageUrls = $this->query->getProductImageUrls($product);
        if (empty($productImageUrls)) {
            return;
        }

        if ($isMaster) {
            $this->resource->addProductImages($arenaProductId, $productImageUrls);
        } else {
            $this->resource->addVariantImages($arenaProductId, $arenaProductVariantId, $productImageUrls);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     * @param int                        $arenaProductVariantId
     */
    protected function updateProductStockQuantity(
        Mage_Catalog_Model_Product $product,
        $arenaProductId,
        $arenaProductVariantId
    ) {
        /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $product->getStockItem();

        $stockLocationData = $this->mapper->getArenaStockLocation();

        $this->resource->updateProductStockQuantity(
            (int) $arenaProductId,
            (int) $arenaProductVariantId,
            (int) $stockLocationData['id'],
            (int) $stockItem->getQty(),
            (bool) $stockItem->getBackorders()
        );
    }

    /**
     * @return array
     */
    public function fullProductResync()
    {
        $productsCollection = $this->query->getProductsCollectionToSync();

        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_PRE_PRODUCT_FULL_RESYNC,
            [
                'products_collection' => $productsCollection,
            ]
        );

        $errors = [];

        foreach ($productsCollection as $product) {
            try {
                $this->exportProduct($product);
            } catch (\Exception $e) {
                $errors[$product->getEntityId()] = [
                    'product' => $product,
                    'exception' => $e,
                ];
            }
        }

        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_POST_PRODUCT_FULL_RESYNC,
            [
                'products_collection' => $productsCollection,
                'errors' => $errors,
            ]
        );

        return $errors;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function exportProductProperties(Mage_Catalog_Model_Product $product)
    {
        $mappedProductAttributes = $this->mapper->getMappedProductAttributes($product);
        if (empty($mappedProductAttributes)) {
            return;
        }

        $arenaProductId = $this->query->getArenaProductId($product);
        $productData = $this->resource->getArenaProductData($arenaProductId);
        if (empty($productData['product_properties'])) {
            return;
        }

        $filteredProductAttributes = [];

        foreach ($product->getAttributes() as $attribute) {
            $attributeId = (int) $attribute->getAttributeId();
            if ($attributeId && isset($mappedProductAttributes[$attributeId])) {
                $filteredProductAttributes[$attributeId] = $attribute;
            }
        }

        $flippedMappedProductAttrs = array_flip($mappedProductAttributes);

        foreach ($productData['product_properties'] as $data) {
            if (isset($flippedMappedProductAttrs[$data['property_name']])
                && isset($filteredProductAttributes[$flippedMappedProductAttrs[$data['property_name']]])
            ) {
                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $attribute = $filteredProductAttributes[$flippedMappedProductAttrs[$data['property_name']]];

                $productValue = $product->getData($attribute->getAttributeCode());

                $attributeOptions = $attribute->usesSource() ? $attribute->getSource()->getAllOptions() : [];
                foreach ($attributeOptions as $labelValue) {
                    if ($labelValue['value'] === $productValue) {
                        $productValue = $labelValue['label'];
                        break;
                    }
                }

                $this->resource->saveArenaProductProperty(
                    $arenaProductId,
                    $data['id'],
                    $productValue
                );
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function exportProductOptionValues(Mage_Catalog_Model_Product $product)
    {
        $arenaProductId = $this->query->getArenaProductId($product);
        $productData = $this->resource->getArenaProductData($arenaProductId);
        if (!is_array($productData)) {
            return;
        }

        $mappedProductAttributes = $this->mapper->getMappedProductAttributes($product);
        if (empty($mappedProductAttributes)) {
            return;
        }

        $mappedAttributeOptions = $this->mapper->getMappedAttributeOptions(
            array_keys($mappedProductAttributes)
        );
        if (empty($mappedAttributeOptions)) {
            return;
        }

        $mappedAttributes = [];
        $mappedAttributesIds = array_keys($mappedAttributeOptions);

        foreach ($product->getAttributes() as $attribute) {
            $attributeId = (int) $attribute->getAttributeId();
            if (in_array($attributeId, $mappedAttributesIds)) {
                $mappedAttributes[$attributeId] = $attribute;
            }
        }

        $productOptionValuesNames = [];

        foreach ($mappedAttributeOptions as $attributeId => $mappedOptions) {
            if (isset($mappedAttributes[$attributeId])) {
                $code = $mappedAttributes[$attributeId]->getAttributeCode();
                $attributeValue = $product->getData($code);
                if (isset($mappedOptions[$attributeValue])) {
                    $productOptionValuesNames[] = $mappedOptions[$attributeValue];
                }
            }
        }

        $prototypeTaxonId = current($productData['taxon_ids']);
        $prototypeCategory = null;

        /* @var $category Mage_Catalog_Model_Category */
        foreach ($this->query->getProductCategoryCollection($product) as $category) {
            if ((int) $category->getArenaTaxonId() === $prototypeTaxonId) {
                $prototypeCategory = $category;
                break;
            }
        }

        if (!$prototypeCategory) {
            return;
        }

        $translatedOptionTypes = [];
        $prototypeTaxonomyId = (int) $prototypeCategory->getArenaTaxonomyId();
        $prototype = $this->mapper->getTaxonPrototype(
            $prototypeTaxonomyId,
            $prototypeTaxonId
        );

        if (empty($prototype['spree_option_types'])) {
            return;
        }

        foreach ($prototype['spree_option_types'] as $optionType) {
            foreach ($optionType['spree_option_values'] as $optionValue) {
                if (in_array($optionValue['name'], $productOptionValuesNames)) {
                    $translatedOptionTypes[] = $optionValue['id'];
                }
            }
        }

        if (empty($translatedOptionTypes)) {
            return;
        }

        $this->resource->saveArenaProductVariantOptionValues(
            $arenaProductId,
            $productData['master']['id'],
            $translatedOptionTypes
        );
    }
}
