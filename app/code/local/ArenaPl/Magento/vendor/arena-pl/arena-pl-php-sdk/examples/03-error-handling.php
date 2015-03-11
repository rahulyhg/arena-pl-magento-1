<?php

use ArenaPl\ApiCall\ApiCallInterface;

require_once '../vendor/autoload.php';

/**
 * Funkcja zwraca tablice zawierajaca wynik wywolania i komunikat wyjatku.
 *
 * Uzywac list($result, $exceptionMessage) = sandboxedApiCall($apiCall);
 *
 * @param ApiCallInterface $apiCall
 *
 * @return array
 */
function sandboxedApiCall(ApiCallInterface $apiCall)
{
    try {
        $result = $apiCall->getResult();

        return [$result, null];
    } catch (\Exception $e) {
        return [null, $e->getMessage()];
    }
}

// $client utworzony w 01-creating-sdk-client.php

$getProducts = $client->getProducts();

// konfiguracja $getProducts
// zapytanie zostanie wywolane,
// a tresc ewentualnego wyjatku zapisana w $exceptionMessage
list($result, $exceptionMessage) = sandboxedApiCall($getProducts);

if (null === $exceptionMessage) {
    // zapytanie wykonane poprawnie

    echo $result;
} else {
    // zapytanie niepoprawne

    echo $exceptionMessage;
}
