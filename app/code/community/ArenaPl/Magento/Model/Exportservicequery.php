<?php

class ArenaPl_Magento_Model_Exportservicequery
{
    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    /**
     * @var ArenaPl_Magento_Model_Resource_Exportservice
     */
    protected $resource;

    /**
     * @param ArenaPl_Magento_Model_Resource_Exportservice $resource
     * @param ArenaPl_Magento_Model_Mapper                 $mapper
     */
    public function __construct(
        ArenaPl_Magento_Model_Resource_Exportservice $resource,
        ArenaPl_Magento_Model_Mapper $mapper
    ) {
        $this->resource = $resource;
        $this->mapper = $mapper;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public static function getProductCategoryCollection(Mage_Catalog_Model_Product $product)
    {
        $collection = $product->getCategoryCollection();
        $collection->addAttributeToSelect(ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATEGORY_ARENA_TAXONOMY_PERMALINK);
        $collection->addAttributeToSelect(ArenaPl_Magento_Model_Mapper::ATTRIBUTE_CATEGORY_ARENA_TAXON_PERMALINK);

        return $collection;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isAnyProductCategoryMapped(Mage_Catalog_Model_Product $product)
    {
        $collection = self::getProductCategoryCollection($product);

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
     * @return bool
     */
    public function isProductOnStock(Mage_Catalog_Model_Product $product)
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
    public function productShouldBeArenaMaster(Mage_Catalog_Model_Product $product)
    {                
        return $this->resource->isProductTypeConfigurable($product)
            || empty($this->resource->getParentsIdsByChildId($product->getId()))
            || $this->isProductColorVariant($product);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isProductArenaVariant(Mage_Catalog_Model_Product $product)
    {
        return $this->resource->isProductTypeSimple($product)
            && !empty($this->resource->getParentsIdsByChildId($product->getId()))
            && !$this->isProductColorVariant($product);
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isProductColorVariant(Mage_Catalog_Model_Product $product)
    {
        return !empty($product->getData('color'));
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isProductExported(Mage_Catalog_Model_Product $product)
    {
        return $this->getArenaProductId($product) != 0
            && $this->getArenaProductVariantId($product);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int
     */
    public function getArenaProductId(Mage_Catalog_Model_Product $product)
    {
        return (int) $product->getArenaProductId();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int
     */
    public function getArenaProductVariantId(Mage_Catalog_Model_Product $product)
    {
        return (int) $product->getArenaProductVariantId();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return Mage_Catalog_Model_Product|null
     *
     * @throws \RuntimeException When given product has more than 1 parent
     */
    public function getProductParent(Mage_Catalog_Model_Product $product)
    {
        $parentIds = $this->resource->getParentsIdsByChildId($product->getId());

        if (empty(($parentIds))) {
            return;
        } elseif (count($parentIds) > 1) {
            throw new \RuntimeException('Product "%s" has multiple parents');
        }

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addAttributeToFilter('entity_id', ['eq' => current($parentIds)]);
        $collection->addAttributeToSelect('*');

        $collectionArray = iterator_to_array($collection);
        if (empty($collectionArray)) {
            return;
        }

        return current($collectionArray);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    public function getProductImageUrls(Mage_Catalog_Model_Product $product)
    {
        /* @var $images Varien_Data_Collection */
        $images = $product->getMediaGalleryImages();

        if (empty($images)) {
            return [];
        }

        /* @var $mediaAttributes Mage_Catalog_Model_Resource_Eav_Attribute[] */
        $mediaAttributes = $product->getMediaAttributes();

        /* @var $mediaConfig Mage_Catalog_Model_Product_Media_Config */
        $mediaConfig = $product->getMediaConfig();

        $imageUrls = [];
        $avoidWhenIterating = [];

        // base image always goes first
        $baseImage = isset($mediaAttributes['image']) ? $product->getData('image') : null;
        if ($baseImage) {
            $imageUrls[] = $mediaConfig->getMediaUrl($baseImage);
            $avoidWhenIterating[] = current($imageUrls);
        }

        $smallImage = isset($mediaAttributes['small_image']) ? $product->getData('small_image') : null;
        if ($smallImage) {
            $smallImage = $mediaConfig->getMediaUrl($smallImage);
            $avoidWhenIterating[] = $smallImage;
        }

        $thumbnail = isset($mediaAttributes['thumbnail']) ? $product->getData('thumbnail') : null;
        if ($thumbnail) {
            $thumbnail = $mediaConfig->getMediaUrl($thumbnail);
            $avoidWhenIterating[] = $thumbnail;
        }

        /* @var $image Varien_Object */
        foreach ($images as $image) {
            $imageUrl = $image->getData('url');
            if (empty($imageUrl) || in_array($imageUrl, $avoidWhenIterating)) {
                continue;
            }
            $imageUrls[] = $imageUrl;
        }

        if ($smallImage) {
            $imageUrls[] = $smallImage;
        }

        if ($thumbnail && empty($imageUrls)) {
            $imageUrls[] = $thumbnail;
        }

        return array_unique($imageUrls);
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getProductsCollectionToSync()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addAttributeToSelect('*');

        return $collection;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array [property_id => product_value]
     */
    public function getProductProperties(Mage_Catalog_Model_Product $product)
    {
        $mappedProductAttributes = $this->mapper->getMappedProductAttributes($product);
        if (empty($mappedProductAttributes)) {
            return [];
        }

        $arenaProductId = $this->getArenaProductId($product);

        $productData = $this->resource->getArenaProductData($arenaProductId);
        if (empty($productData['product_properties'])) {
            return [];
        }

        $filteredProductAttributes = [];

        foreach ($product->getAttributes() as $attribute) {
            $attributeId = (int) $attribute->getAttributeId();
            if ($attributeId && isset($mappedProductAttributes[$attributeId])) {
                $filteredProductAttributes[$attributeId] = $attribute;
            }
        }

        $flippedMappedProductAttrs = array_flip($mappedProductAttributes);

        $returnValues = [];

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

                $returnValues[$data['id']] = $productValue;
            }
        }

        return $returnValues;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     *
     * @return int[]
     */
    public function getProductOptionValues(
        Mage_Catalog_Model_Product $product,
        $arenaProductId
    ) {
        $productData = $this->resource->getArenaProductData($arenaProductId);
        if (!is_array($productData)) {
            return [];
        }

        $mappedProductAttributes = $this->mapper->getMappedProductAttributes($product);
        if (empty($mappedProductAttributes)) {
            return [];
        }

        $mappedAttributeOptions = $this->mapper->getMappedAttributeOptions(
            array_keys($mappedProductAttributes)
        );
        if (empty($mappedAttributeOptions)) {
            return [];
        }

        $mappedAttributes = [];
        $mappedAttributesIds = array_keys($mappedAttributeOptions);

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
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
        if (empty($productOptionValuesNames)) {
            return [];
        }

        $prototypeTaxonId = current($productData['taxon_ids']);
        $prototypeCategoryData = null;

        /* @var $category Mage_Catalog_Model_Category */
        foreach (self::getProductCategoryCollection($product) as $category) {
            $categoryTaxonData = $this->mapper->getMappedArenaTaxon($category);
            if ($categoryTaxonData['taxon_id'] === $prototypeTaxonId) {
                $prototypeCategoryData = $categoryTaxonData;
                break;
            }
        }

        if (empty($prototypeCategoryData)) {
            return [];
        }

        $prototype = $this->mapper->getTaxonPrototype(
            $prototypeCategoryData['taxonomy_id'],
            $prototypeTaxonId
        );

        if (empty($prototype['spree_option_types'])) {
            return [];
        }

        $translatedOptionTypes = [];

        foreach ($prototype['spree_option_types'] as $optionType) {
            foreach ($optionType['spree_option_values'] as $optionValue) {
                if (in_array($optionValue['name'], $productOptionValuesNames)) {
                    $translatedOptionTypes[] = $optionValue['id'];
                }
            }
        }

        return $translatedOptionTypes;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    public function getProductSiblings(Mage_Catalog_Model_Product $product)
    {
        $collection = new Varien_Data_Collection();
        
        $parent = $this->getProductParent($product);
        if ($parent instanceof Mage_Catalog_Model_Product) {
            $childrenIds = $this->resource->getChildrenIdsByParentId($parent->getId());
            $keysToRemove = array_keys($childrenIds, $product->getId());
            $productSiblingsIds = array_diff_key($childrenIds, $keysToRemove);
        }
        
        if (!empty($productSiblingsIds)) {
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $productCollection = Mage::getModel('catalog/product')->getCollection();
            
            $productCollection->addAttributeToFilter('entity_id', ['eq' => array_unique($productSiblingsIds)]);
            $productCollection->addAttributeToSelect('*');
        
            foreach($productCollection as $sibling) {
                $collection->addItem($sibling);
            }
        }
        
        return $collection;
    }
}
