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

                $productOptionValues = $this->query->getProductOptionValues($product, $arenaProductId);
                if (!empty($productOptionValues)) {
                    $this->resource->exportExistingVariantProduct(
                        $product,
                        $arenaProductId,
                        $arenaProductVariantId,
                        $productOptionValues
                    );
                }
            }
        } else {
            if ($isMaster) {
                list($arenaProductId, $arenaProductVariantId) = $this->resource->exportNewProduct($product);
            } else {
                try {
                    $arenaProductId = $this->ensureMasterProductExported($product);

                    $productOptionValues = $this->query->getProductOptionValues($product, $arenaProductId);
                    if (!empty($productOptionValues)) {
                        $arenaProductVariantId = $this->resource->exportNewProductVariant(
                            $product,
                            $arenaProductId,
                            $productOptionValues
                        );
                    }
                } catch (\Exception $e) {
                    Mage::logException($e);
                }
            }

            if ($arenaProductId && $arenaProductVariantId) {
                $this->saveArenaProductId($product, $arenaProductId, $arenaProductVariantId);
            }
        }

        if ($arenaProductId && $arenaProductVariantId) {
            $this->updateProductStockQuantity($product, $arenaProductId, $arenaProductVariantId);
            $this->exportImages(
                $product,
                $arenaProductId,
                $arenaProductVariantId,
                $isMaster
            );
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
     * @param bool                       $isMaster
     */
    protected function exportProductEmptyStock(Mage_Catalog_Model_Product $product, $isMaster)
    {
        if ($this->query->isProductExported($product)) {
            $arenaProductId = $this->query->getArenaProductId($product);
            $arenaProductVariantId = $this->query->getArenaProductVariantId($product);

            if ($isMaster) {
                $this->resource->ensureArenaProductMasterVisible($arenaProductId);
            } else {
                $this->resource->ensureArenaProductVariantVisible($arenaProductId, $arenaProductVariantId);
            }

            $this->updateProductStockQuantity($product, $arenaProductId, $arenaProductVariantId);
            $this->exportImages(
                $product,
                $arenaProductId,
                $arenaProductVariantId,
                $isMaster
            );

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
     * @param bool                       $isMaster
     */
    protected function exportImages(
        Mage_Catalog_Model_Product $product,
        $arenaProductId,
        $arenaProductVariantId,
        $isMaster
    ) {
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
            $this->resource->addVariantImages($arenaProductVariantId, $productImageUrls);
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
        $productProperties = $this->query->getProductProperties($product);

        if (empty($productProperties)) {
            return;
        }

        $arenaProductId = $this->query->getArenaProductId($product);

        foreach ($productProperties as $propertyId => $productValue) {
            $this->resource->saveArenaProductProperty(
                $arenaProductId,
                $propertyId,
                $productValue
            );
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function exportProductOptionValues(Mage_Catalog_Model_Product $product)
    {
        $productOptionValues = $this->query->getProductProperties($product);

        if (empty($productOptionValues)) {
            return;
        }

        $this->resource->saveArenaProductVariantOptionValues(
            $this->query->getArenaProductId($product),
            $this->query->getArenaProductVariantId($product),
            $productOptionValues
        );
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     */
    protected function setProductColorRelations(Mage_Catalog_Model_Product $product)
    {
        $productSiblings = $this->query->getProductSiblings($product);
        
        $productArenaId = $this->query->getArenaProductId($product);
        if (!$productArenaId) {
            return;
        }
        
        $idsToRelate = [];
        foreach($productSiblings as $sibling) {
            if ($this->query->isProductColorVariant($sibling)) {
                $siblingArenaId = $this->query->getArenaProductId($sibling);
                if ($siblingArenaId && $siblingArenaId != $productArenaId) {
                    $idsToRelate[] = $siblingArenaId;
                }
            }
        }
        
        if (empty($idsToRelate)) {
            return;
        }
        
        $this->resource->setProductsRelation($productArenaId,$idsToRelate);
    }
}
