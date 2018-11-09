<?php
require __DIR__ . '/../../../vendor/autoload.php';

$pheetsu = \Ttskch\Pheetsu\Factory\PheetsuFactory::createServiceAccount(
    __DIR__ . '/../../service-account-credentials.json',
    '1JQkfd3dlyxFRuxIwGPnBnrxS-l-bLVw_BbHskxT9Nj4',
    'demo'
);
/** @see https://docs.google.com/spreadsheets/d/1JQkfd3dlyxFRuxIwGPnBnrxS-l-bLVw_BbHskxT9Nj4/edit#gid=0 */

$pheetsu->authenticate();

// read
$rows = $pheetsu->read();
var_dump($rows);

// search
$rows = $pheetsu->search([
    'name' => '*a*',
    'age' => '1*',
], 0, 0, true);
var_dump($rows);

/* demo spreadsheet is read-only for everyone.

// create
$pheetsu->create([
    'id' => 4,
    'name' => 'Dave',
    'age' => 16,
]);
var_dump($pheetsu->read());

// update
$rows = $pheetsu->update('name', 'Dave', [
    'id' => 10,
    'age' => 100,
]);
var_dump($rows);

// delete
$pheetsu->delete('name', 'Dave');
var_dump($pheetsu->read());
*/
