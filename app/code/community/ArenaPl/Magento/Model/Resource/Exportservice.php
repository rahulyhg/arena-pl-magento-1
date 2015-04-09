<?php

use ArenaPl\ApiCall\ApiCallInterface;
use ArenaPl\Client;

class ArenaPl_Magento_Model_Resource_Exportservice
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    /**
     * @var Mage_Catalog_Model_Resource_Product_Relation
     */
    protected $productsRelation;

    public function __construct()
    {
        $this->mapper = Mage::getSingleton('arenapl_magento/mapper');
        $this->client = Mage::helper('arenapl_magento')->getClient();
        $this->productsRelation = Mage::getResourceSingleton('catalog/product_relation');
    }

    /**
     * @param int $arenaProductId
     *
     * @return bool
     */
    public function ensureArenaProductMasterVisible($arenaProductId)
    {
        try {
            return $this->client->restoreArchivedProduct()
                ->setProductId($arenaProductId)
                ->getResult();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param int $arenaProductId
     * @param int $arenaProductVariantId
     *
     * @return bool
     */
    public function ensureArenaProductVariantVisible($arenaProductId, $arenaProductVariantId)
    {
        try {
            return $this->client->restoreArchivedProductVariant()
                ->setProductId($arenaProductId)
                ->setProductVariantId($arenaProductVariantId)
                ->getResult();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param int $arenaProductId
     */
    public function archiveProduct($arenaProductId)
    {
        try {
            $this->client->archiveProduct()
                ->setProductId($arenaProductId)
                ->getResult();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param int $arenaProductId
     * @param int $arenaProductVariantId
     */
    public function archiveProductVariant($arenaProductId, $arenaProductVariantId)
    {
        try {
            $this->client->archiveProductVariant()
                ->setProductId($arenaProductId)
                ->setProductVariantId($arenaProductVariantId)
                ->getResult();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int[]|null[]
     */
    public function exportNewProduct(Mage_Catalog_Model_Product $product)
    {
        $productData = $this->prepareArenaCompatibleProductData($product);

        try {
            $apiCall = $this->client->createProduct()
                ->setProductData($productData);

            $result = $apiCall->getResult();

            return (empty($result['id']) || empty($result['master']['id']))
                ? [null, null]
                : [(int) $result['id'], (int) $result['master']['id']];
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     * @param int[]                      $productOptionValues
     *
     * @return int|null
     */
    public function exportNewProductVariant(
        Mage_Catalog_Model_Product $product,
        $arenaProductId,
        array $productOptionValues
    ) {
        $variantData = $this->prepareArenaCompatibleProductVariantData($product);

        try {
            $apiCall = $this->client->createProductVariant()
                ->setVariantData($variantData)
                ->setOptionValueIds($productOptionValues)
                ->setProductId($arenaProductId);

            $result = $apiCall->getResult();

            return empty($result['id']) ? null : (int) $result['id'];
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isProductTypeSimple(Mage_Catalog_Model_Product $product)
    {
        return $product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isProductTypeConfigurable(Mage_Catalog_Model_Product $product)
    {
        return $product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    public function exportExistingMasterProduct(
        Mage_Catalog_Model_Product $product,
        $arenaProductId
    ) {
        $productData = $this->prepareArenaCompatibleProductData($product);

        try {
            $this->client->updateProduct()
                ->setProductId($arenaProductId)
                ->setProductData($productData)
                ->getResult();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     * @param int                        $arenaProductVariantId
     * @param int[]                      $productOptionValues
     */
    public function exportExistingVariantProduct(
        Mage_Catalog_Model_Product $product,
        $arenaProductId,
        $arenaProductVariantId,
        array $productOptionValues
    ) {
        $variantData = $this->prepareArenaCompatibleProductVariantData($product);

        try {
            $this->client->updateProductVariant()
                ->setVariantData($variantData)
                ->setOptionValueIds($productOptionValues)
                ->setProductVariantId($arenaProductVariantId)
                ->setProductId($arenaProductId)
                ->getResult();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function prepareArenaCompatibleProductData(Mage_Catalog_Model_Product $product)
    {
        $description = $product->getDescription();
        $sku = $product->getSku();
        $weight = $product->getWeight();

        $data = [
            'name' => (string) $product->getName(),
            'external_product_id' => (int) $product->getId(),
            'price' => $this->getArenaCompatiblePrice($product),
            'description' => empty($description) ? '' : (string) $description,
            'sku' => empty($sku) ? '' : (string) $sku,
            'weight' => empty($weight) ? '' : (float) $weight,
        ];

        $taxonIds = [];
        $collection = ArenaPl_Magento_Model_Exportservicequery::getProductCategoryCollection($product);

        /* @var $category Mage_Catalog_Model_Category */
        foreach ($collection as $category) {
            if ($this->mapper->hasMappedTaxon($category)) {
                $taxonData = $this->mapper->getMappedArenaTaxon($category);
                if (!empty($taxonData['taxon_id'])) {
                    $taxonIds[] = (int) $taxonData['taxon_id'];
                }
            }
        }
        $data['taxon_ids'] = $taxonIds;

        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return array
     */
    protected function prepareArenaCompatibleProductVariantData($product)
    {
        $sku = $product->getSku();

        $data = [
            'external_product_id' => (int) $product->getId(),
            'price' => $this->getArenaCompatiblePrice($product),
            'sku' => empty($sku) ? '' : (string) $sku,
        ];

        return $data;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    protected function getArenaCompatiblePrice(Mage_Catalog_Model_Product $product)
    {
        $arenaCompatiblePrice = preg_replace(
            '/\./', ',',
            $product->getPrice(),
            1
        );

        return (string) $arenaCompatiblePrice;
    }

    /**
     * @param int $arenaProductId
     */
    public function deleteExistingArenaMasterImages($arenaProductId)
    {
        $productData = $this->getArenaProductData($arenaProductId);
        if (empty($productData['master']['images'])) {
            return;
        }

        $apiCall = $this->client
            ->deleteProductImage()
            ->setProductId($arenaProductId);

        foreach ($productData['master']['images'] as $image) {
            try {
                $apiCall
                    ->setProductImageId((int) $image['id'])
                    ->getResult();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param int $arenaProductId
     * @param int $arenaProductVariantId
     */
    public function deleteExistingArenaVariantImages($arenaProductId, $arenaProductVariantId)
    {
        $variantData = $this->getArenaProductVariantData($arenaProductId, $arenaProductVariantId);
        if (empty($variantData['images'])) {
            return;
        }

        $apiCall = $this->client
            ->deleteProductImage()
            ->setProductId($arenaProductId);

        foreach ($variantData['images'] as $image) {
            try {
                $apiCall
                    ->setProductImageId((int) $image['id'])
                    ->getResult();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param int $arenaProductId
     *
     * @return array|null
     */
    public function getArenaProductData($arenaProductId)
    {
        try {
            return $this->client->getProduct()
                ->setProductId((int) $arenaProductId)
                ->getResult();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param int $arenaProductId
     * @param int $arenaProductVariantId
     *
     * @return array|null
     */
    public function getArenaProductVariantData($arenaProductId, $arenaProductVariantId)
    {
        $productData = $this->getArenaProductData($arenaProductId);
        if (empty($productData)) {
            return;
        }

        if ($productData['master']['id'] == $arenaProductVariantId) {
            return $productData['master'];
        }

        foreach ($productData['variants'] as $variantData) {
            if ($variantData['id'] == $arenaProductVariantId) {
                return $variantData;
            }
        }
    }

    /**
     * @param int      $arenaProductId
     * @param string[] $imageUrls
     */
    public function addProductImages($arenaProductId, array $imageUrls)
    {
        $apiCall = $this->client
            ->createProductImage()
            ->setProductId($arenaProductId);

        foreach ($imageUrls as $url) {
            try {
                $apiCall
                    ->setProductImageUrl($url)
                    ->getResult();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param int      $arenaProductVariantId
     * @param string[] $imageUrls
     */
    public function addVariantImages($arenaProductVariantId, array $imageUrls)
    {
        $apiCall = $this->client
            ->createProductVariantImage()
            ->setProductVariantId($arenaProductVariantId);

        foreach ($imageUrls as $url) {
            try {
                $apiCall
                    ->setProductVariantImageUrl($url)
                    ->getResult();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param int  $arenaProductId
     * @param int  $arenaProductVariantId
     * @param int  $stockLocationId
     * @param int  $qty
     * @param bool $allowBackorders
     *
     * @return bool
     */
    public function updateProductStockQuantity(
        $arenaProductId,
        $arenaProductVariantId,
        $stockLocationId,
        $qty,
        $allowBackorders
    ) {
        $stockItemData = $this->getStockItemData(
            $arenaProductId,
            $arenaProductVariantId,
            $stockLocationId
        );

        if (!is_array($stockItemData)) {
            return false;
        }

        $stockItemId = (int) $stockItemData['id'];

        return $this->updateStockItemData(
            $stockItemId,
            $stockLocationId,
            $qty,
            $allowBackorders
        );
    }

    /**
     * @param int $arenaProductId
     * @param int $arenaProductVariantId
     * @param int $stockLocationId
     *
     * @return array|null
     */
    protected function getStockItemData(
        $arenaProductId,
        $arenaProductVariantId,
        $stockLocationId
    ) {
        $foundStockItems = $this->findStockItems($arenaProductVariantId, $stockLocationId);
        if (empty($foundStockItems)) {
            return;
        }

        return current($foundStockItems);
    }

    /**
     * @param int $variantId
     * @param int $stockLocationId
     * @param int $resultsPerPage
     *
     * @return array|null
     */
    protected function findStockItems(
        $variantId,
        $stockLocationId,
        $resultsPerPage = 1000
    ) {
        try {
            return $this->client->getStockItems()
                ->setResultsPerPage((int) $resultsPerPage)
                ->setStockLocationId((int) $stockLocationId)
                ->setSearch(
                    'variant_id',
                    (int) $variantId,
                    ApiCallInterface::SEARCH_METHOD_EQUALS
                )
                ->getResult();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param int  $stockItemId
     * @param int  $qty
     * @param bool $allowBackorders
     *
     * @return bool
     */
    protected function updateStockItemData(
        $stockItemId,
        $stockLocationId,
        $qty,
        $allowBackorders
    ) {
        try {
            return $this->client->updateStockItem()
                ->setStockItemId((int) $stockItemId)
                ->setStockLocationId((int) $stockLocationId)
                ->setCountOnHand((int) $qty)
                ->setStockItemField('backorderable', (bool) $allowBackorders)
                ->getResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int    $arenaProductId
     * @param int    $propertyId
     * @param scalar $value
     */
    public function saveArenaProductProperty(
        $arenaProductId,
        $propertyId,
        $value
    ) {
        try {
            return $this->client->setProductProperty()
                ->setProductId((int) $arenaProductId)
                ->setProductPropertyId((int) $propertyId)
                ->setPropertyValue($value)
                ->getResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int   $arenaProductId
     * @param int   $variantId
     * @param int[] $optionValuesIds
     *
     * @return array|bool
     */
    public function saveArenaProductVariantOptionValues(
        $arenaProductId,
        $variantId,
        array $optionValuesIds
    ) {
        try {
            return $this->client->updateProductVariant()
                ->setOptionValueIds($optionValuesIds)
                ->setProductVariantId((int) $variantId)
                ->setProductId((int) $arenaProductId)
                ->getResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns array of product parent IDs.
     *
     * @param int $childId
     *
     * @return int[]
     */
    public function getParentsIdsByChildId($childId)
    {
        $read = ArenaPl_Magento_Helper_Data::getDBReadConnection();
        $select = $read->select()
            ->distinct()
            ->from($this->productsRelation->getMainTable(), 'parent_id')
            ->where('child_id=?', $childId);

        return $read->fetchCol($select);
    }
    
    /**
     * Returns array of product parent IDs.
     *
     * @param int $parentId
     *
     * @return int[]
     */
    public function getChildrenIdsByParentId($parentId)
    {
        $read = ArenaPl_Magento_Helper_Data::getDBReadConnection();
        $select = $read->select()
            ->distinct()
            ->from($this->productsRelation->getMainTable(), 'child_id')
            ->where('parent_id=?', $parentId);

        return $read->fetchCol($select);
    }
    
    /**
     * @param int $arenaProductId
     * @param int[] $productsIdsToRelate
     */
    public function setProductsRelation(
        $arenaProductId,
        array $productsIdsToRelate
    ) {
        try {
            return $this->client->setProductRelatedProducts()
                ->setRelatedProductIds($productsIdsToRelate)
                ->setProductId((int) $arenaProductId)
                ->getResult();
        } catch (\Exception $e) {
            return false;
        }
    }
}
