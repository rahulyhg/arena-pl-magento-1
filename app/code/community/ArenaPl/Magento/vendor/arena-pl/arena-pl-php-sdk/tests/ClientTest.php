<?php

namespace ArenaPl\Test;

use ArenaPl\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        $this->client = new Client('test.host', 'test token');
    }

    public function testClientWillReturnApiCallExecutor()
    {
        $this->assertInstanceOf(
            '\ArenaPl\ApiCallExecutor\ApiCallExecutorInterface',
            $this->client->getApiCallExecutor()
        );
    }

    /**
     * @dataProvider apiCallObjectsProvider
     */
    public function testClientReturnsProperApiCallObjects(
        $expectedObject,
        $methodToCall
    ) {
        $this->assertInstanceOf(
            $expectedObject,
            $this->client->$methodToCall()
        );
    }

    public function apiCallObjectsProvider()
    {
        return [
            ['\ArenaPl\ApiCall\ArchiveProduct', 'archiveProduct'],
            ['\ArenaPl\ApiCall\ArchiveProductVariant', 'archiveProductVariant'],
            ['\ArenaPl\ApiCall\CreateProduct', 'createProduct'],
            ['\ArenaPl\ApiCall\CreateProductImage', 'createProductImage'],
            ['\ArenaPl\ApiCall\CreateProductVariant', 'createProductVariant'],
            ['\ArenaPl\ApiCall\DeleteProductImage', 'deleteProductImage'],
            ['\ArenaPl\ApiCall\GetProduct', 'getProduct'],
            ['\ArenaPl\ApiCall\GetProducts', 'getProducts'],
            ['\ArenaPl\ApiCall\GetStockItems', 'getStockItems'],
            ['\ArenaPl\ApiCall\GetStockItem', 'getStockItem'],
            ['\ArenaPl\ApiCall\GetStockLocation', 'getStockLocation'],
            ['\ArenaPl\ApiCall\GetStockLocations', 'getStockLocations'],
            ['\ArenaPl\ApiCall\GetTaxon', 'getTaxon'],
            ['\ArenaPl\ApiCall\GetTaxonTree', 'getTaxonTree'],
            ['\ArenaPl\ApiCall\GetTaxonomies', 'getTaxonomies'],
            ['\ArenaPl\ApiCall\RestoreArchivedProduct', 'restoreArchivedProduct'],
            ['\ArenaPl\ApiCall\SetProductProperty', 'setProductProperty'],
            ['\ArenaPl\ApiCall\SetProductRelatedProducts', 'setProductRelatedProducts'],
            ['\ArenaPl\ApiCall\UpdateProduct', 'updateProduct'],
            ['\ArenaPl\ApiCall\UpdateProductVariant', 'updateProductVariant'],
            ['\ArenaPl\ApiCall\UpdateStockItem', 'updateStockItem'],
            ['\ArenaPl\ApiCall\GetOrders', 'getOrders'],
            ['\ArenaPl\ApiCall\GetOrder', 'getOrder'],
            ['\ArenaPl\ApiCall\GetOrderPayments', 'getOrderPayments'],
            ['\ArenaPl\ApiCall\GetOrderPayment', 'getOrderPayment'],
            ['\ArenaPl\ApiCall\CaptureOrderPayment', 'captureOrderPayment'],
            ['\ArenaPl\ApiCall\UpdateShipmentState', 'updateShipmentState'],
            ['\ArenaPl\ApiCall\UpdateShipmentTracking', 'updateShipmentTracking'],
            ['\ArenaPl\ApiCall\GetShippingCategories', 'getShippingCategories'],
        ];
    }
}
