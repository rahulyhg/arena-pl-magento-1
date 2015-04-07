<?php

namespace ArenaPl;

use ArenaPl\ApiCall\ApiCallFactory;
use ArenaPl\ApiCall\ArchiveProduct;
use ArenaPl\ApiCall\ArchiveProductVariant;
use ArenaPl\ApiCall\CaptureOrderPayment;
use ArenaPl\ApiCall\CreateProduct;
use ArenaPl\ApiCall\CreateProductImage;
use ArenaPl\ApiCall\CreateProductVariant;
use ArenaPl\ApiCall\DeleteProductImage;
use ArenaPl\ApiCall\GetOrder;
use ArenaPl\ApiCall\GetOrderPayment;
use ArenaPl\ApiCall\GetOrderPayments;
use ArenaPl\ApiCall\GetOrders;
use ArenaPl\ApiCall\GetProduct;
use ArenaPl\ApiCall\GetProducts;
use ArenaPl\ApiCall\GetShippingCategories;
use ArenaPl\ApiCall\GetStockItem;
use ArenaPl\ApiCall\GetStockItems;
use ArenaPl\ApiCall\GetStockLocation;
use ArenaPl\ApiCall\GetStockLocations;
use ArenaPl\ApiCall\GetTaxon;
use ArenaPl\ApiCall\GetTaxonomies;
use ArenaPl\ApiCall\GetTaxonTree;
use ArenaPl\ApiCall\RestoreArchivedProduct;
use ArenaPl\ApiCall\RestoreArchivedProductVariant;
use ArenaPl\ApiCall\SetProductProperty;
use ArenaPl\ApiCall\SetProductRelatedProducts;
use ArenaPl\ApiCall\UpdateProduct;
use ArenaPl\ApiCall\UpdateProductVariant;
use ArenaPl\ApiCall\UpdateShipmentState;
use ArenaPl\ApiCall\UpdateShipmentTracking;
use ArenaPl\ApiCall\UpdateStockItem;
use ArenaPl\ApiCallExecutor\ApiCallExecutorFactory;
use ArenaPl\ApiCallExecutor\ApiCallExecutorInterface;

/**
 * Main SDK class.
 *
 * Best used with dependency injection container.
 */
class Client
{
    /**
     * Executor performing API calls.
     *
     * @var ApiCallExecutorInterface
     */
    protected $apiCallExecutor;

    /**
     * Factory constructing API call objects.
     *
     * @var ApiCallFactory
     */
    protected $apiCallFactory;

    /**
     * @param string $subdomain
     * @param string $token
     * @param bool   $forceHttp
     *
     * @return self
     */
    public function __construct($subdomain, $token, $forceHttp = false)
    {
        $this->apiCallExecutor = $this->initApiCallExecutor(
            $subdomain,
            $token,
            $forceHttp
        );

        $this->apiCallFactory = $this->initApiCallFactory();

        return $this;
    }

    /**
     * @return ApiCallExecutorInterface
     */
    public function getApiCallExecutor()
    {
        return $this->apiCallExecutor;
    }

    /**
     * @api
     *
     * @return GetTaxonomies
     */
    public function getTaxonomies()
    {
        return $this->apiCallFactory->getApiCall('GetTaxonomies');
    }

    /**
     * @api
     *
     * @return GetTaxon
     */
    public function getTaxon()
    {
        return $this->apiCallFactory->getApiCall('GetTaxon');
    }

    /**
     * @api
     *
     * @return GetTaxonTree
     */
    public function getTaxonTree()
    {
        return $this->apiCallFactory->getApiCall('GetTaxonTree');
    }

    /**
     * @api
     *
     * @return GetStockItems
     */
    public function getStockItems()
    {
        return $this->apiCallFactory->getApiCall('GetStockItems');
    }

    /**
     * @api
     *
     * @return GetStockItem
     */
    public function getStockItem()
    {
        return $this->apiCallFactory->getApiCall('GetStockItem');
    }

    /**
     * @api
     *
     * @return GetStockLocation
     */
    public function getStockLocation()
    {
        return $this->apiCallFactory->getApiCall('GetStockLocation');
    }

    /**
     * @api
     *
     * @return GetStockLocations
     */
    public function getStockLocations()
    {
        return $this->apiCallFactory->getApiCall('GetStockLocations');
    }

    /**
     * @api
     *
     * @return GetProducts
     */
    public function getProducts()
    {
        return $this->apiCallFactory->getApiCall('GetProducts');
    }

    /**
     * @api
     *
     * @return GetProduct
     */
    public function getProduct()
    {
        return $this->apiCallFactory->getApiCall('GetProduct');
    }

    /**
     * @api
     *
     * @return CreateProduct
     */
    public function createProduct()
    {
        return $this->apiCallFactory->getApiCall('CreateProduct');
    }

    /**
     * @api
     *
     * @return UpdateProduct
     */
    public function updateProduct()
    {
        return $this->apiCallFactory->getApiCall('UpdateProduct');
    }

    /**
     * @api
     *
     * @return ArchiveProduct
     */
    public function archiveProduct()
    {
        return $this->apiCallFactory->getApiCall('ArchiveProduct');
    }

    /**
     * @api
     *
     * @return RestoreArchivedProduct
     */
    public function restoreArchivedProduct()
    {
        return $this->apiCallFactory->getApiCall('RestoreArchivedProduct');
    }

    /**
     * @api
     *
     * @return RestoreArchivedProductVariant
     */
    public function restoreArchivedProductVariant()
    {
        return $this->apiCallFactory->getApiCall('RestoreArchivedProductVariant');
    }

    /**
     * @api
     *
     * @return ArchiveProductVariant
     */
    public function archiveProductVariant()
    {
        return $this->apiCallFactory->getApiCall('ArchiveProductVariant');
    }

    /**
     * @api
     *
     * @return CreateProductImage
     */
    public function createProductImage()
    {
        return $this->apiCallFactory->getApiCall('CreateProductImage');
    }

    /**
     * @api
     *
     * @return CreateProductVariant
     */
    public function createProductVariant()
    {
        return $this->apiCallFactory->getApiCall('CreateProductVariant');
    }

    /**
     * @api
     *
     * @return UpdateStockItem
     */
    public function updateStockItem()
    {
        return $this->apiCallFactory->getApiCall('UpdateStockItem');
    }

    /**
     * @api
     *
     * @return UpdateProductVariant
     */
    public function updateProductVariant()
    {
        return $this->apiCallFactory->getApiCall('UpdateProductVariant');
    }

    /**
     * @api
     *
     * @return DeleteProductImage
     */
    public function deleteProductImage()
    {
        return $this->apiCallFactory->getApiCall('DeleteProductImage');
    }

    /**
     * @api
     *
     * @return SetProductProperty
     */
    public function setProductProperty()
    {
        return $this->apiCallFactory->getApiCall('SetProductProperty');
    }

    /**
     * @api
     *
     * @return GetOrders
     */
    public function getOrders()
    {
        return $this->apiCallFactory->getApiCall('GetOrders');
    }

    /**
     * @api
     *
     * @return GetOrder
     */
    public function getOrder()
    {
        return $this->apiCallFactory->getApiCall('GetOrder');
    }

    /**
     * @api
     *
     * @return GetOrderPayments
     */
    public function getOrderPayments()
    {
        return $this->apiCallFactory->getApiCall('GetOrderPayments');
    }

    /**
     * @api
     *
     * @return GetOrderPayment
     */
    public function getOrderPayment()
    {
        return $this->apiCallFactory->getApiCall('GetOrderPayment');
    }

    /**
     * @api
     *
     * @return SetProductRelatedProducts
     */
    public function setProductRelatedProducts()
    {
        return $this->apiCallFactory->getApiCall('SetProductRelatedProducts');
    }

    /**
     * @api
     *
     * @return CaptureOrderPayment
     */
    public function captureOrderPayment()
    {
        return $this->apiCallFactory->getApiCall('CaptureOrderPayment');
    }

    /**
     * @api
     *
     * @return UpdateShipmentState
     */
    public function updateShipmentState()
    {
        return $this->apiCallFactory->getApiCall('UpdateShipmentState');
    }

    /**
     * @api
     *
     * @return UpdateShipmentTracking
     */
    public function updateShipmentTracking()
    {
        return $this->apiCallFactory->getApiCall('UpdateShipmentTracking');
    }

    /**
     * @api
     *
     * @return GetShippingCategories
     */
    public function getShippingCategories()
    {
        return $this->apiCallFactory->getApiCall('GetShippingCategories');
    }

    /**
     * @param string $subdomain
     * @param string $token
     * @param bool   $forceHttp
     *
     * @return ApiCallExecutorInterface
     */
    protected function initApiCallExecutor($subdomain, $token, $forceHttp)
    {
        $executorFactory = new ApiCallExecutorFactory(
            $subdomain,
            $token,
            $forceHttp
        );

        return $executorFactory->getApiCallExecutor();
    }

    /**
     * @return ApiCallFactory
     */
    protected function initApiCallFactory()
    {
        return new ApiCallFactory($this->apiCallExecutor);
    }
}
