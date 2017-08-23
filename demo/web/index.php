<?php
require __DIR__ . '/../../vendor/autoload.php';

$parameters = require __DIR__ . '/../parameters.php';

$pheetsu = \Ttskch\Pheetsu\Factory\PheetsuFactory::create(
    $parameters['client_id'],
    $parameters['client_secret'],
    $parameters['redirect_uri'],
    $parameters['javascript_origin'],
    '1JQkfd3dlyxFRuxIwGPnBnrxS-l-bLVw_BbHskxT9Nj4',
    'demo'
);
/** @see https://docs.google.com/spreadsheets/d/1JQkfd3dlyxFRuxIwGPnBnrxS-l-bLVw_BbHskxT9Nj4/edit#gid=0 */

$pheetsu->authenticate();

$pheetsu->read('A1:C4');
