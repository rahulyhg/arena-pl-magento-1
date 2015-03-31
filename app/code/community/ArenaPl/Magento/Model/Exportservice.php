<?php

class ArenaPl_Magento_Model_Exportservice extends Mage_Core_Model_Abstract
{
    /**
     * EAV product attribute.
     */
    const ATTRIBUTE_PRODUCT_ARENA_ID = 'arena_product_id';

    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    /**
     * @var ArenaPl_Magento_Model_Resource_Exportservice
     */
    protected $resource;

    protected function _construct()
    {
        $this->resource = Mage::getResourceSingleton('arenapl_magento/exportservice');
        $this->mapper = Mage::getSingleton('arenapl_magento/mapper');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function exportProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$this->isAnyProductCategoryMapped($product)) {
            return;
        }

        if ($this->isProductOnStock($product)) {
            $this->exportProductOnStock($product);
        } else {
            $this->exportProductEmptyStock($product);
        }

        $this->exportProductProperties($product);
        $this->exportProductOptionValues($product);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function exportProductOnStock(Mage_Catalog_Model_Product $product)
    {
        if ($this->isProductExported($product)) {
            $arenaProductId = $this->getArenaProductId($product);

            $this->resource->ensureArenaProductVisible($arenaProductId);
            $this->resource->exportExistingProduct($product, $arenaProductId);
        } else {
            $arenaProductId = $this->resource->exportNewProduct($product);
            if ($arenaProductId) {
                $this->saveArenaProductId($product, $arenaProductId);
            }
        }

        if ($arenaProductId) {
            $this->updateProductStockQuantity($product, $arenaProductId);
            $this->exportImages($product, $arenaProductId);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function exportProductEmptyStock(Mage_Catalog_Model_Product $product)
    {
        if ($this->isProductExported($product)) {
            $arenaProductId = $this->getArenaProductId($product);

            $this->updateProductStockQuantity($product, $arenaProductId);
            $this->resource->archiveProduct($arenaProductId);
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    protected function isAnyProductCategoryMapped(Mage_Catalog_Model_Product $product)
    {
        $collection = $this->getProductCategoryCollection($product);

        /* @var $category Mage_Catalog_Model_Category */
        foreach ($collection as $category) {
            if ($this->mapper->hasMappedTaxon($category)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public static function getProductCategoryCollection(Mage_Catalog_Model_Product $product)
    {
        $collection = $product->getCategoryCollection();
        $collection->addAttributeToSelect(ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATEGORY_ARENA_TAXONOMY_ID);
        $collection->addAttributeToSelect(ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATEGORY_ARENA_TAXON_ID);

        return $collection;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    protected function isProductOnStock(Mage_Catalog_Model_Product $product)
    {
        /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $product->getStockItem();

        $stockQuantity = (int) $stockItem->getIsInStock();

        return $stockQuantity > 0;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    protected function isProductExported(Mage_Catalog_Model_Product $product)
    {
        return $this->getArenaProductId($product) != 0;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int
     */
    protected function getArenaProductId(Mage_Catalog_Model_Product $product)
    {
        return (int) $product->getArenaProductId();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    protected function saveArenaProductId(
        Mage_Catalog_Model_Product $product,
        $arenaProductId
    ) {
        $product->setData(self::ATTRIBUTE_PRODUCT_ARENA_ID, (int) $arenaProductId);
        $product->save();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    protected function exportImages(Mage_Catalog_Model_Product $product, $arenaProductId)
    {
        $this->resource->deleteExistingArenaImages($arenaProductId);

        $productImageUrls = $this->getProductImageUrls($product);
        if (empty($productImageUrls)) {
            return;
        }

        $this->resource->addProductImages($arenaProductId, $productImageUrls);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function getProductImageUrls(Mage_Catalog_Model_Product $product)
    {
        $imageUrls = [];

        /* @var $images Varien_Data_Collection */
        $images = $product->getMediaGalleryImages();

        /* @var $image Varien_Object */
        foreach ($images as $image) {
            $imageUrl = $image->getData('url');
            if (!empty($imageUrl)) {
                $imageUrls[] = (string) $imageUrl;
            }
        }

        return $imageUrls;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    protected function updateProductStockQuantity(
        Mage_Catalog_Model_Product $product,
        $arenaProductId
    ) {
        /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $product->getStockItem();

        $stockLocationData = $this->mapper->getArenaStockLocation();

        $this->resource->updateProductStockQuantity(
            (int) $arenaProductId,
            (int) $stockLocationData['id'],
            (int) $stockItem->getQty(),
            (bool) $stockItem->getBackorders()
        );
    }

    /**
     * @return string
     */
    public function fullProductResync()
    {
        $productsCollection = $this->getProductsCollectionToSync();

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
                $errors[$product->getEntityId()] = $e;
            }
        }

        Mage::dispatchEvent(
            ArenaPl_Magento_EventInterface::EVENT_POST_PRODUCT_FULL_RESYNC,
            [
                'products_collection' => $productsCollection,
                'errors' => $errors,
            ]
        );

        return empty($errors) ? 'ok' : sprintf('%d errors', count($errors));
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getProductsCollectionToSync()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addAttributeToSelect('*');

        return $collection;
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

        $filteredProductAttributes = [];

        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {
            $attributeId = (int) $attribute->getAttributeId();
            if ($attributeId && isset($mappedProductAttributes[$attributeId])) {
                $filteredProductAttributes[$attributeId] = $attribute;
            }
        }

        $flippedMappedProductAttrs = array_flip($mappedProductAttributes);

        $arenaProductId = (int) $product->getArenaProductId();

        $productData = $this->resource->getArenaProductData($arenaProductId);
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

        $mappedAttributesIds = array_keys($mappedAttributeOptions);
        $mappedAttributes = [];
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

        $arenaProductId = (int) $product->getArenaProductId();

        $productData = $this->resource->getArenaProductData($arenaProductId);
        if (!is_array($productData)) {
            return;
        }

        $prototypeTaxonId = current($productData['taxon_ids']);
        $prototypeCategory = null;

        /* @var $category Mage_Catalog_Model_Category */
        foreach (self::getProductCategoryCollection($product) as $category) {
            if ((int) $category->getArenaTaxonId() === $prototypeTaxonId) {
                $prototypeCategory = $category;
                break;
            }
        }

        if (!$prototypeCategory) {
            return;
        }

        $prototypeTaxonomyId = (int) $prototypeCategory->getArenaTaxonomyId();
        $prototype = $this->mapper->getTaxonPrototype($prototypeTaxonomyId, $prototypeTaxonId);

        $translatedOptionTypes = [];
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
