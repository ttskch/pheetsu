<?php

namespace Ttskch\Pheetsu;

use Ttskch\GoogleSheetsApi\Client;
use Ttskch\Pheetsu\Exception\RuntimeException;
use Ttskch\Pheetsu\Service\ColumnNameResolver;

class Pheetsu
{
    const MAX_ROW = 9999999;
    const MAX_COLUMN = 'ZZZ';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ColumnNameResolver
     */
    private $columnNameResolver;

    /**
     * @var string
     */
    private $spreadsheetId;

    /**
     * @var string
     */
    private $sheetName;

    /**
     * @var AuthenticationHelper
     */
    private $authenticationHelper;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var string
     */
    private $lastColumn;

    /**
     * @param Client $client
     * @param AuthenticationHelper $authenticationHelper
     * @param ColumnNameResolver $columnNameResolver
     * @param $spreadsheetId
     * @param $sheetName
     */
    public function __construct(Client $client, ColumnNameResolver $columnNameResolver, $spreadsheetId, $sheetName, AuthenticationHelper $authenticationHelper = null)
    {
        $this->client = $client;
        $this->columnNameResolver = $columnNameResolver;
        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;
        $this->keys = [];
        $this->authenticationHelper = $authenticationHelper;
    }

    /**
     * @param bool $forceApprovalPrompt
     */
    public function authenticate($forceApprovalPrompt = false)
    {
        if ($this->authenticationHelper) {
            $this->authenticationHelper->authenticate($forceApprovalPrompt);
        }
    }

    /**
     * @return \Google_Service_Oauth2_Userinfoplus
     */
    public function getUserInfo()
    {
        $googleClient = $this->client->getGoogleService()->getClient();
        $oauth = new \Google_Service_Oauth2($googleClient);

        return $oauth->userinfo_v2_me->get();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @see https://docs.sheetsu.com/?shell#read-all-data
     */
    public function read($limit = 0, $offset = 0)
    {
        $range = sprintf('%s!A1:%s%s', $this->sheetName, $this->getLastColumn(), self::MAX_ROW);
        $response = $this->client->getGoogleService()->spreadsheets_values->get($this->spreadsheetId, $range);
        $rows = $response->getValues();

        $keys = array_shift($rows);

        $rows = array_slice($rows, $offset, $limit ?: null);

        foreach ($rows as $i => $row) {
            $row = array_merge($row, array_fill(0, count($keys) - count($row), ''));
            $rows[$i] = array_combine($keys, $row);
        }

        return $rows;
    }

    /**
     * @param array $query
     * @param int $limit
     * @param int $offset
     * @param bool $ignoreCase
     * @return array
     * @see https://docs.sheetsu.com/?shell#search-spreadsheet
     */
    public function search(array $query, $limit = 0, $offset = 0, $ignoreCase = false)
    {
        $rows = [];

        foreach ($this->read() as $row) {

            $matched = false;

            foreach ($query as $queryKey => $queryValue) {

                // if query key doesn't exist do nothing.
                if (
                    ($ignoreCase && !array_key_exists(strtolower($queryKey), array_change_key_case($row, CASE_LOWER))) ||
                    (!$ignoreCase && !isset($row[$queryKey]))
                ) {
                    continue;
                }

                $actualValue = array_change_key_case($row, CASE_LOWER)[strtolower($queryKey)];

                // '*' in query value is a wildcard.
                if (strpos($queryValue, '*') !== false) {
                    $queryRegExp = sprintf('/%s/%s', str_replace('*', '.*', str_replace('/', '\/', $queryValue)), $ignoreCase ? 'i' : '');
                    $matched = preg_match($queryRegExp, $actualValue);
                } else {
                    $matched = ($queryValue === $actualValue);
                }
            }

            if ($matched) {
                $rows[] = $row;
            }
        }

        $rows = array_slice($rows, $offset, $limit ?: null);

        return $rows;
    }

    /**
     * @param array $rows
     * @return array
     * @see https://docs.sheetsu.com/?shell#create
     */
    public function create(array $rows)
    {
        if (!is_array(reset($rows))) {
            $rows = [$rows];
        }

        $flattenedRows = [];

        foreach ($rows as $row) {
            $flattenedRows[] = $this->flattenRow($row);
        }

        $range = sprintf('%s!A1:%s1', $this->sheetName, $this->getLastColumn());

        $valueRange = new \Google_Service_Sheets_ValueRange();
        $valueRange->setMajorDimension('ROWS');
        $valueRange->setValues($flattenedRows);

        $params = [
            'valueInputOption' => 'USER_ENTERED',
        ];

        /**
         * @see https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/append
         * @see https://developers.google.com/sheets/samples/writing
         */
        $this->client->getGoogleService()->spreadsheets_values->append($this->spreadsheetId, $range, $valueRange, $params);

        return $rows;
    }

    /**
     * @param $columnName
     * @param $value
     * @param array $row
     * @param bool $updateWholeRow
     * @return array
     * @see https://docs.sheetsu.com/?shell#update
     */
    public function update($columnName, $value, array $row, $updateWholeRow = false)
    {
        $postRows = $updatedRows = [];

        foreach ($this->read() as $readRow) {
            if (isset($readRow[$columnName]) && $readRow[$columnName] === $value) {
                // '' for clear cell, null for skip cell.
                $postRows[] = $this->flattenRow($row, $updateWholeRow ? '' : \Google_Model::NULL_VALUE);

                $callback = function ($k) { return in_array($k, $this->getKeys()); };
                $updatedRows[] = array_filter($row, $callback, ARRAY_FILTER_USE_KEY) + $readRow;
            } else {
                // [] for skip row.
                $postRows[] = [];

                $updatedRows[] = null;
            }
        }

        $range = sprintf('%s!A2', $this->sheetName);

        $valueRange = new \Google_Service_Sheets_ValueRange();
        $valueRange->setMajorDimension('ROWS');
        $valueRange->setValues($postRows);

        $params = [
            'valueInputOption' => 'USER_ENTERED',
        ];

        /**
         * @see https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/update
         * @see https://developers.google.com/sheets/samples/writing
         */
        $this->client->getGoogleService()->spreadsheets_values->update($this->spreadsheetId, $range, $valueRange, $params);

        return array_filter($updatedRows);
    }

    /**
     * @param $columnName
     * @param $value
     */
    public function delete($columnName, $value)
    {
        $this->update($columnName, $value, [], true);
    }

    /**
     * @param array $row
     * @param string $padding
     * @return array
     */
    public function flattenRow(array $row, $padding = '')
    {
        $flattened = [];

        foreach ($this->getKeys() as $key) {
            $flattened[] = array_key_exists($key, $row) ? $row[$key] : $padding;
        }

        return $flattened;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys ?: $this->scanKeys();
    }

    /**
     * @return array
     */
    public function scanKeys()
    {
        $range = sprintf('%s!A1:%s1', $this->sheetName, $this->getLastColumn());
        $response = $this->client->getGoogleService()->spreadsheets_values->get($this->spreadsheetId, $range);
        $rows = $response->getValues();

        $this->keys = $rows[0];

        return $this->keys;
    }

    /**
     * @return string
     */
    private function getLastColumn()
    {
        return $this->lastColumn ?: $this->scanLastColumn();
    }

    /**
     * @return string
     */
    private function scanLastColumn()
    {
        $range = sprintf('%s!A1:%s1', $this->sheetName, self::MAX_COLUMN);
        $response = $this->client->getGoogleService()->spreadsheets_values->get($this->spreadsheetId, $range);
        $rows = $response->getValues();

        $this->lastColumn = $this->columnNameResolver->getName(count($rows[0]));

        return $this->lastColumn;
    }
}
