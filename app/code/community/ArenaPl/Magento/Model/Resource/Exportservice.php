<?php

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
            $apiCall = $this->client->restoreArchivedProduct();
            $apiCall->setProductId($arenaProductId);

            return $apiCall->getResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int $arenaProductId
     */
    public function archiveProduct($arenaProductId)
    {
        try {
            $apiCall = $this->client->archiveProduct();
            $apiCall->setProductId($arenaProductId);

            $apiCall->getResult();
        } catch (\Exception $e) {
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
            $apiCall = $this->client->createProduct();
            $apiCall->setProductData($productData);

            $result = $apiCall->getResult();

            return empty($result['id']) ? null : (int) $result['id'];
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    public function exportExistingProduct(Mage_Catalog_Model_Product $product, $arenaProductId)
    {
        $productData = $this->prepareArenaCompatibleData($product);

        try {
            $apiCall = $this->client->updateProduct();
            $apiCall->setProductId($arenaProductId);
            $apiCall->setProductData($productData);

            $apiCall->getResult();
        } catch (\Exception $e) {
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
        $collection = $this->getProductCategoryCollection($product);

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

        $apiCall = $this->client->deleteProductImage();
        $apiCall->setProductId($arenaProductId);

        foreach ($productData['master']['images'] as $image) {
            try {
                $apiCall->setProductImageId((int) $image['id']);

                $apiCall->getResult();
            } catch (\Exception $e) {
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
            $apiCall = $this->client->getProduct();
            $apiCall->setProductId((int) $arenaProductId);

            return $apiCall->getResult();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param int      $arenaProductId
     * @param string[] $imageUrls
     */
    public function addProductImages($arenaProductId, array $imageUrls)
    {
        $apiCall = $this->client->createProductImage();
        $apiCall->setProductId($arenaProductId);

        foreach ($imageUrls as $url) {
            try {
                $apiCall->setProductImageUrl($url);

                $apiCall->getResult();
            } catch (\Exception $e) {
            }
        }
    }
}
