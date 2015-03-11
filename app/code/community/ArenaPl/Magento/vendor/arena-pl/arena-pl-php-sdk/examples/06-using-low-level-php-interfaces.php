<?php

require_once '../vendor/autoload.php';

// $client utworzony w 01-creating-sdk-client.php

// wykorzystanie \Countable

    $getProducts = $client->getProducts();
    $getProducts->setResultsPerPage(1000);

    // sprawdzenie czy obiekt implementuje interfejs
    if ($getProducts instanceof \Countable) {
        echo 'Istnieja produkty: ' . (empty($getProducts) ? 'nie' : 'tak') . '<br>';
        echo 'Liczba produktow: ' . count($getProducts);
    }

// wykorzystanie \IteratorAggregate

    // sprawdzenie czy obiekt implementuje interfejs
    if ($getProducts instanceof \Traversable) {

        // iterowanie po produktach
        foreach ($getProducts as $product) {
            var_dump($product);
        }
    }
