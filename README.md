# pheetsu

[![Latest Stable Version](https://poser.pugx.org/ttskch/pheetsu/v/stable)](https://packagist.org/packages/ttskch/pheetsu)
[![Total Downloads](https://poser.pugx.org/ttskch/pheetsu/downloads)](https://packagist.org/packages/ttskch/pheetsu)

PHP library to CRUDify Google Spreadsheets like [sheetsu.com](https://sheetsu.com).

## Requirements

- PHP 5.6+

## Installations

```bash
$ composer require ttskch/pheetsu:@dev
```

## Usage

If you have a Google Spreadsheet like [this](https://docs.google.com/spreadsheets/d/1JQkfd3dlyxFRuxIwGPnBnrxS-l-bLVw_BbHskxT9Nj4/edit#gid=0),

![image](https://user-images.githubusercontent.com/4360663/31042852-2c4fca34-a5ec-11e7-83e0-b048ed3fe3c8.png)

You can CRUD the spreadsheet via pheetsu so easily like below.

```php
$pheetsu = \Ttskch\Pheetsu\Factory\PheetsuFactory::create(
    'google_oauth2_client_id',
    'google_oauth2_client_secret',
    'google_oauth2_redirect_uri',
    'google_oauth2_javascript_origin',
    '1JQkfd3dlyxFRuxIwGPnBnrxS-l-bLVw_BbHskxT9Nj4', // spreadsheet id
    'demo' // sheet name
);

// authenticate and be authorized with Google OAuth2.
$pheetsu->authenticate();

$rows = $pheetsu->read();
var_dump($rows);

// array (size=3)
//   0 => 
//     array (size=3)
//       'id' => string '1' (length=1)
//       'name' => string 'Alice' (length=5)
//       'age' => string '20' (length=2)
//   1 => 
//     array (size=3)
//       'id' => string '2' (length=1)
//       'name' => string 'Bob' (length=3)
//       'age' => string '25' (length=2)
//   2 => 
//     array (size=3)
//       'id' => string '3' (length=1)
//       'name' => string 'Charlie' (length=7)
//       'age' => string '18' (length=2)
```

See also [demo](demo).
