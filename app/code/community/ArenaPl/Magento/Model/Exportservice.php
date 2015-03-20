<?php

use ArenaPl\Client;

class ArenaPl_Magento_Model_Exportservice extends Mage_Core_Model_Abstract
{
    const ATTRIBUTE_PRODUCT_ARENA_ID = 'arena_product_id';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ArenaPl_Magento_Helper_Data
     */
    protected $helper;

    /**
     * @var ArenaPl_Magento_Model_Mapper
     */
    protected $mapper;

    protected function _construct()
    {
        $this->helper = Mage::helper('arenapl_magento');
        $this->mapper = Mage::getSingleton('arenapl_magento/mapper');
        $this->client = $this->helper->getClient();
    }

    public function exportProduct(Mage_Catalog_Model_Product $product)
    {
        if (!$this->isAnyProductCategoryMapped($product)) {
            return;
        }

        if ($this->isProductExported($product)) {
            $arenaProductId = $this->exportExistingProduct($product);
        } else {
            $arenaProductId = $this->exportNewProduct($product);
            if ($arenaProductId) {
                $this->saveArenaProductId($product, $arenaProductId);
            }
        }

        if ($arenaProductId) {
            $this->exportImages($product, $arenaProductId);
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
    protected function getProductCategoryCollection(Mage_Catalog_Model_Product $product)
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
    protected function isProductExported(Mage_Catalog_Model_Product $product)
    {
        $arenaProductId = (int) $product->getArenaProductId();

        return $arenaProductId != 0;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int|null
     */
    protected function exportNewProduct(Mage_Catalog_Model_Product $product)
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
     *
     * @return bool
     */
    protected function exportExistingProduct(Mage_Catalog_Model_Product $product)
    {
        $productData = $this->prepareArenaCompatibleData($product);
        $arenaProductId = (int) $product->getArenaProductId();

        try {
            $apiCall = $this->client->updateProduct();
            $apiCall->setProductId($arenaProductId);
            $apiCall->setProductData($productData);

            $apiCall->getResult();

            return $arenaProductId;
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
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    protected function saveArenaProductId(Mage_Catalog_Model_Product $product, $arenaProductId)
    {
        $product->setData(self::ATTRIBUTE_PRODUCT_ARENA_ID, (int) $arenaProductId);
        $product->save();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param int                        $arenaProductId
     */
    protected function exportImages(Mage_Catalog_Model_Product $product, $arenaProductId)
    {
        $this->deleteExistingArenaImages($arenaProductId);

        $productImageUrls = $this->getProductImageUrls($product);
        if (empty($productImageUrls)) {
            return;
        }

        $apiCall = $this->client->createProductImage();
        $apiCall->setProductId($arenaProductId);

        foreach ($productImageUrls as $url) {
            try {
                $apiCall->setProductImageUrl($url);

                $apiCall->getResult();
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param int $arenaProductId
     */
    protected function deleteExistingArenaImages($arenaProductId)
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
    protected function getArenaProductData($arenaProductId)
    {
        try {
            $apiCall = $this->client->getProduct();
            $apiCall->setProductId((int) $arenaProductId);

            return $apiCall->getResult();
        } catch (\Exception $e) {
        }
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
}
