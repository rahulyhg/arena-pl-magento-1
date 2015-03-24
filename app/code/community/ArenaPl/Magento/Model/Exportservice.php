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
}
