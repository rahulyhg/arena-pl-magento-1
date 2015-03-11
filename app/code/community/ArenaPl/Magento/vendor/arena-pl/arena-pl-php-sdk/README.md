## Arena.pl PHP SDK

||||
|:---:|:---:|:---:|
| [![Build Status](https://travis-ci.org/arena-pl/arena-pl-php-sdk.svg?branch=master)](https://travis-ci.org/arena-pl/arena-pl-php-sdk) | [![Code Climate](https://codeclimate.com/github/arena-pl/arena-pl-php-sdk/badges/gpa.svg)](https://codeclimate.com/github/arena-pl/arena-pl-php-sdk) | [![Test Coverage](https://codeclimate.com/github/arena-pl/arena-pl-php-sdk/badges/coverage.svg)](https://codeclimate.com/github/arena-pl/arena-pl-php-sdk) |
| [![Latest Stable Version](https://poser.pugx.org/arena-pl/arena-pl-php-sdk/v/stable.svg)](https://packagist.org/packages/arena-pl/arena-pl-php-sdk) | [![Total Downloads](https://poser.pugx.org/arena-pl/arena-pl-php-sdk/downloads.svg)](https://packagist.org/packages/arena-pl/arena-pl-php-sdk) | [![License](https://poser.pugx.org/arena-pl/arena-pl-php-sdk/license.svg)](https://packagist.org/packages/arena-pl/arena-pl-php-sdk) |

#### Polska dokumentacja dostępna na [GitHub Wiki](https://github.com/arena-pl/arena-pl-php-sdk/wiki)

### Installation

The recommended way to install SDK is through [Composer](http://getcomposer.org).

If your application is using Composer already, all you need to do is add  
`"arena-pl/arena-pl-php-sdk": "~1.0@stable"` to your composer.json `require` section.  
Then run update command:
```bash
composer update
```

If Composer is not available you can install it manually:
```bash
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest version of SDK:
```bash
composer require arena-pl/arena-pl-php-sdk:~1.0@stable
```

After installing, you need to require Composer's autoloader:
```php
require_once 'vendor/autoload.php';
```

### Usage

```php
// you start with configuring API client 
$client = new \ArenaPl\Client('your_subdomain', 'token');

// every API request has its own object which you can configure
$getTaxonomies = $client->getTaxonomies();

$getTaxonomies->setPage(1);
$getTaxonomies->setResultsPerPage(3);
$getTaxonomies->setSearch('search_name','search_value');
$getTaxonomies->setSort('sort_field', \ArenaPl\ApiCall\ApiCallInterface::SORT_DESC);

// when finished setting request you may call API
try {
    $result = $getTaxonomies->getResult();
    
    // now you can also grab some metadata about response
    $itemsCount = $getTaxonomies->getCount();
    $currentPage = $getTaxonomies->getCurrentPage();
    $allPages = $getTaxonomies->getPages();
    
    // do something with result
    
} catch (\ArenaPl\Exception\ApiCallException $e) {
    // here you can inspect what went wrong   
}
```
