<?php

use ArenaPl\Client;

require_once '../vendor/autoload.php';

// tworzenie instancji klienta
$client = new Client('my-subdomain', 'my-token');

// majac kontener wstrzykujacy zaleznosci np. Pimple http://pimple.sensiolabs.org
// mozna zdefiniowac globalna usluge
//
// $container['arena_sdk_client'] = function ($c) {
//     return new Client('my-subdomain', 'my-token');
// };
