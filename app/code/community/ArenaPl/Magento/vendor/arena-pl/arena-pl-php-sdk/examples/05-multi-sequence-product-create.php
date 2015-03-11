<?php

use ArenaPl\Client;

require_once '../vendor/autoload.php';

/**
 * Klasa tworzy produkt, dodaje zdjecia i option values dla wariantu master.
 */
class ProductCreator
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $productData = [
        'product' => [],
        'image_urls' => [],
        'option_values' => [],
    ];

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $productData
     */
    public function setProductData(array $productData)
    {
        $this->productData['product'] = $productData;
    }

    /**
     * @param array $imageUrls
     */
    public function setProductImages(array $imageUrls)
    {
        $this->productData['image_urls'] = $imageUrls;
    }

    /**
     * @param array $optionValues
     */
    public function setOptionValues(array $optionValues)
    {
        $this->productData['option_values'] = $optionValues;
    }

    /**
     * Dane utworzonego produktu.
     *
     * @return array
     */
    public function createProduct()
    {
        $createProductApi = $this->client->createProduct();
        $createProductApi->setProductData($this->productData['product']);

        $createdProduct = $createProductApi->getResult();

        if ($this->productData['image_urls']) {
            $productImageApi = $this->client->createProductImage();
            $productImageApi->setProductId($createdProduct['id']);

            foreach ($this->productData['image_urls'] as $url) {
                $productImageApi
                    ->setProductImageUrl($url)
                    ->getResult();
            }
        }

        if ($this->productData['option_values']) {
            $productVariantApi = $this->client->updateProductVariant();
            $productVariantApi
                ->setProductId($createdProduct['id'])
                ->setProductVariantId($createdProduct['master']['id'])
                ->setOptionValueIds($this->productData['option_values'])
                ->getResult();
        }

        return $this->client
            ->getProduct()
            ->setProductId($createdProduct['id'])
            ->getResult();
    }
}

// $client utworzony w 01-creating-sdk-client.php

$productCreator = new ProductCreator($client);
$productCreator->setProductData([
    'name' => 'Testowa opona product creator',
    'price' => 7.99,
    'taxon_ids' => [
        1122,
        1431,
    ],
]);
$productCreator->setProductImages([
    'http://image.pl/img1.jpg',
    'http://image.pl/img2.jpg',
]);
$productCreator->setOptionValues([
    3989,
    1992,
]);

var_dump($productCreator->createProduct());
