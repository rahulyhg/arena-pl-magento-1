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

    public function __construct()
    {
        $this->mapper = Mage::getSingleton('arenapl_magento/mapper');
        $this->client = Mage::helper('arenapl_magento')->getClient();
    }

    /**
     * @param int $arenaProductId
     *
     * @return bool
     */
    public function ensureArenaProductVisible($arenaProductId)
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
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int|null
     */
    public function exportNewProduct(Mage_Catalog_Model_Product $product)
    {
        $productData = $this->prepareArenaCompatibleData($product);

        try {
            $apiCall = $this->client->createProduct()
                ->setProductData($productData);

            $result = $apiCall->getResult();

            return empty($result['id']) ? null : (int) $result['id'];
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    public function exportExistingProduct(
        Mage_Catalog_Model_Product $product,
        $arenaProductId
    ) {
        $productData = $this->prepareArenaCompatibleData($product);

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
     *
     * @return array
     */
    protected function prepareArenaCompatibleData(Mage_Catalog_Model_Product $product)
    {
        $data = [
            'name' => (string) $product->getName(),
        ];

        $arenaCompatiblePrice = preg_replace(
            '/\./', ',',
            $product->getPrice(),
            1
        );
        $data['price'] = (string) $arenaCompatiblePrice;

        $taxonIds = [];
        $collection = ArenaPl_Magento_Model_Exportservice::getProductCategoryCollection($product);

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

        $description = $product->getDescription();
        if (!empty($description)) {
            $data['description'] = (string) $description;
        }

        $sku = $product->getSku();
        if (!empty($sku)) {
            $data['sku'] = (string) $sku;
        }

        $weight = $product->getWeight();
        if (!empty($weight)) {
            $data['weight'] = (float) $weight;
        }

        return $data;
    }

    /**
     * @param int $arenaProductId
     */
    public function deleteExistingArenaImages($arenaProductId)
    {
        $productData = $this->getArenaProductData($arenaProductId);
        if (!is_array($productData)) {
            return;
        }

        $apiCall = $this->client->deleteProductImage()
            ->setProductId($arenaProductId);

        foreach ($productData['master']['images'] as $image) {
            try {
                $apiCall->setProductImageId((int) $image['id'])
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
     * @param int      $arenaProductId
     * @param string[] $imageUrls
     */
    public function addProductImages($arenaProductId, array $imageUrls)
    {
        $apiCall = $this->client->createProductImage()
            ->setProductId($arenaProductId);

        foreach ($imageUrls as $url) {
            try {
                $apiCall->setProductImageUrl($url)
                    ->getResult();
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @param int  $arenaProductId
     * @param int  $stockLocationId
     * @param int  $qty
     * @param bool $allowBackorders
     *
     * @return bool
     */
    public function updateProductStockQuantity(
        $arenaProductId,
        $stockLocationId,
        $qty,
        $allowBackorders
    ) {
        $stockItemData = $this->getStockItemData($arenaProductId, $stockLocationId);
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
     * @param int $stockLocationId
     *
     * @return array|null
     */
    protected function getStockItemData($arenaProductId, $stockLocationId)
    {
        $productData = $this->getArenaProductData($arenaProductId);
        if (!is_array($productData)) {
            return;
        }

        $masterVariantId = (int) $productData['master']['id'];
        $foundStockItems = $this->findStockItems($masterVariantId, $stockLocationId);
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
}
