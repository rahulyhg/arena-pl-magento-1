<?php

use ArenaPl\ApiCall\GetTaxon;

require_once '../vendor/autoload.php';

/**
 * Funkcja zwraca tablice id taksonow, ktore nie zawieraja zagniezdzonych taksonow.
 *
 * @param GetTaxon $taxon
 * @param array $taxonIdsCandidates
 *
 * @return array
 */
function getTaxonsWithoutChildren(GetTaxon $taxon, array &$taxonIdsCandidates = [])
{
    $result = $taxon->getResult();
    if (isset($result['root'])) {
        $result = $result['root'];
    }

    if (empty($result['taxons'])) {
        $taxonIdsCandidates[$result['id']] = $result['name'];
    } else {
        foreach ($result['taxons'] as $taxonData) {
            $taxon->setTaxonChildId($taxonData['id']);

            getTaxonsWithoutChildren($taxon, $taxonIdsCandidates);
        }
    }

    return $taxonIdsCandidates;
}

// $client utworzony w 01-creating-sdk-client.php

$taxon = $client->getTaxon();
$taxon->setTaxonId(1);

// przejscie bo zbyt duzej liczbie taksonow moze spowodowac timeout serwera
$taxon->setTaxonChildId(28);

// otrzymujemy tablice taksonow z ID w kluczu i nazwie w wartosci tablicy
var_dump(getTaxonsWithoutChildren($taxon));
