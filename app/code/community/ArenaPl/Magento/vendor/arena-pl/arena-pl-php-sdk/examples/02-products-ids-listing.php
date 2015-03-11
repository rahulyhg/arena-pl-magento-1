<?php

use ArenaPl\Client;

require_once '../vendor/autoload.php';

/**
 * Funkcja zwraca tablice ID wszystkich niezarchiwizowanych produktow.
 *
 * @param Client $client
 *
 * @return int[]
 */
function getAllProductIds(Client $client)
{
    // poczatkowe ustawienie funkcji API
    $getProducts = $client->getProducts();
    $getProducts->setResultsPerPage(100);

    // funkcja anonimowa wyciagajaca ID z tablicy
    $idsExtractor = static function (array $product) {
        return $product['id'];
    };

    // pierwsze wywolanie dajace dostep do metadanych
    $productsData = $getProducts->getResult();

    // wyciagniecie ID produktow po pierwszym wywolaniu
    $productIds = array_map($idsExtractor, $productsData);

    $currentPage = 1;
    $allPages = $getProducts->getPages();

    while ($currentPage != $allPages) {
        $getProducts->setPage(++$currentPage);

        // nowa strona wynikow
        $productsData = $getProducts->getResult();

        // zlaczenie wynikow z poprzednimi
        $productIds = array_merge(
            $productIds,
            array_map($idsExtractor, $productsData)
        );
    }

    // mamy pewnosc, ze ID sa unikalne
    return array_unique($productIds);
}

// $client utworzony w 01-creating-sdk-client.php

$productIds = getAllProductIds($client);

var_dump($productIds);
