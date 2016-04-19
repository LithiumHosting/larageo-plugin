![](https://lithiumhosting.com/images/logo_new_black.png)

# Laravel 5+ Geo IP Package
**from Lithium Hosting**  
We're always open to pull requests, feel free to make this your own or help us make it better.

### Copyright
(c) Lithium Hosting, llc

### License
This library is licensed under the MIT license; you can find a full copy of the license itself in the file /LICENSE

### Requirements
- Laravel 5.2+
- php 5.5.9+
- Knowledge of Laravel and php

### Description

A Laravel package that uses [geoPlugin](http://www.geoplugin.com/webservices/json) web service to fetch information from an IP. It will store in cache the IP information and it will expire in 1 week.

* * *

### Installation

Install this package through Composer. To your composer.json file, add:

```js
    "lithiumdev/larageo-plugin": "~1.0"
```

Next, run the Composer update comand

    $ composer update

Add the service provider to app/config/app.php, within the providers array.

```php
    'providers' => array(
        // ...
        LithiumDev\LaraGeo\ServiceProvider::class,
    ),
```

In the same file `config/app.php` add the alias:

```php
    'aliases' => array(
        //...
        'LaraGeo'   => LithiumDev\LaraGeo\Facade::class,
    ),
```

### Usage

You can specify an IP:

```php
    $info = LaraGeo::getInfo('177.34.13.248'); // get info from a IP
    var_dump($info);
```

Or use it without any param:

```php
    $info = LaraGeo::getInfo(); // get info from the IP of the user accessing the page
    var_dump($info);
```

This is the output:

```php
    object(stdClass)[155]
      public 'geoplugin_request' => string '177.34.13.248' (length=13)
      public 'geoplugin_status' => int 200
      public 'geoplugin_credit' => string 'Some of the returned data includes GeoLite data created by MaxMind, available from <a href=\'http://www.maxmind.com\'>http://www.maxmind.com</a>.' (length=145)
      public 'geoplugin_city' => string 'Campo Grande' (length=12)
      public 'geoplugin_region' => string 'Mato Grosso do Sul' (length=18)
      public 'geoplugin_areaCode' => string '0' (length=1)
      public 'geoplugin_dmaCode' => string '0' (length=1)
      public 'geoplugin_countryCode' => string 'BR' (length=2)
      public 'geoplugin_countryName' => string 'Brazil' (length=6)
      public 'geoplugin_continentCode' => string 'SA' (length=2)
      public 'geoplugin_latitude' => string '-20.450001' (length=10)
      public 'geoplugin_longitude' => string '-54.616699' (length=10)
      public 'geoplugin_regionCode' => string '11' (length=2)
      public 'geoplugin_regionName' => string 'Mato Grosso do Sul' (length=18)
      public 'geoplugin_currencyCode' => string 'BRL' (length=3)
      public 'geoplugin_currencySymbol' => string '&#82;&#36;' (length=10)
      public 'geoplugin_currencySymbol_UTF8' => string 'R$' (length=2)
      public 'geoplugin_currencyConverter' => float 2.383
```

Another useful example: You can also just return one field, e.g. city from in one call:

```php
    $userCity = LaraGeo::getInfo()->geoplugin_city; // get the city from the user IP
    var_dump($userCity);
```

Output:

```php
    string 'Campo Grande' (length=12)
```

### More info

If you want more info about the geoPlugin web service, [click here](http://www.geoplugin.com/webservices).
