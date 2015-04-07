<?php

use ArenaPl\ApiCall\UpdateShipmentState;
use ArenaPl\Client;

require_once '../vendor/autoload.php';

/**
 * Funkcja ustawia wysylke zamowienia w pozadany stan.
 *
 * Opcjonalnie mozna ustawic nr sledzenia przesylki.
 *
 * @param Client $client
 * @param string $orderNumber nr zamowienia
 * @param string $state stan, w ktory ustawic wysylke (ready|ship)
 * @param string|null $trackingNumber nr sledzenia wysylki
 *
 * @return bool false jesli wysylka jest w innym stanie niz oczekiwana
 *
 * @throws RuntimeException jesli podano nieznany stan lub do zamowienia
 *                          nie ma wysylek/jest wiecej niz 1 wysylka
 */
function setOrderShipmentState(
    Client $client,
    $orderNumber,
    $state,
    $trackingNumber = null
) {
    $orderApi = $client->getOrder();
    $orderApi->setOrderNumber($orderNumber);

    $orderData = $orderApi->getResult();

    // aby zmienic stan wysylka musi byc w okreslonym stanie
    $expectedStateArray = [
        UpdateShipmentState::SHIPMENT_STATE_READY => 'pending',
        UpdateShipmentState::SHIPMENT_STATE_SHIP => 'ready',
    ];

    // sprawdzenie czy podano prawidlowy stan
    if (!isset($expectedStateArray[$state])) {
        throw new \RuntimeException(sprintf(
            'Unknown state "%s", use one of "%s"',
            $state,
            implode(', ', array_keys($expectedStateArray))
        ));
    }

    // jesli nie ma wysylek lub istnieje wiecej niz 1 wysylka to rzuc wyjatek
    if (empty($orderData['shipments'])) {
        throw new \RuntimeException('No shipment found');
    } elseif (count($orderData['shipments']) > 1) {
        // w razie potrzeby przebudowac na tablice numerow wysylek
        // i wykonywac sprawdzenia i zmiany stanow w petli
        throw new \RuntimeException(sprintf(
            '%d shimpents found', count($orderData['shipments'])
        ));
    }

    $shipmentData = current($orderData['shipments']);

    // wartosc inna niz null uznawana jest za powod do uaktualnienia nru sledzenia
    if ($trackingNumber !== null) {
        $trackingNumberApi = $client->updateShipmentTracking();
        $trackingNumberApi
            ->setShipmentNumber($shipmentData['number'])
            ->setTracking($trackingNumber)
            ->getResult();
    }

    // jesli wysylka znajduje sie w oczekiwanym stanie to przeprowadz zmiane stanu
    if ($shipmentData['state'] == $expectedStateArray[$state]) {
        $shimpentUpdateApi = $client->updateShipmentState();
        $shimpentUpdateApi
            ->setShipmentNumber($shipmentData['number'])
            ->setShipmentState($state)
            ->getResult();

        return true;
    }

    return false;
}

// $client utworzony w 01-creating-sdk-client.php

$result = setOrderShipmentState(
    $client,
    'R447614016',
    UpdateShipmentState::SHIPMENT_STATE_READY,
    'tracking number here'
);
