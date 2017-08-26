# pheetsu

**[WIP]** PHP library to CRUDify Google Spreadsheets like [sheetsu.com](https://sheetsu.com).

## Requirements

- PHP 5.6+

## Installations

```bash
$ composer require ttskch/pheetsu:@dev
```

## Usage

```php
$pheetsu = \Ttskch\Pheetsu\Factory\PheetsuFactory::create(
    'google_oauth2_client_id',
    'google_oauth2_client_secret',
    'google_oauth2_redirect_uri',
    'google_oauth2_javascript_origin',
    'spreadsheet_id',
    'sheet_name'
);

// authenticate and be authorized with Google OAuth2.
$pheetsu->authenticate();

$rows = $pheetsu->read();
var_dump($rows);
```

See also [demo](demo).
