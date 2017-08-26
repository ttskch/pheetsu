<?php

namespace Ttskch\Pheetsu;

use Ttskch\GoogleSheetsApi\Client;
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
     * @var AuthenticationHelper
     */
    private $authenticationHelper;

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
     * @param Client $client
     * @param AuthenticationHelper $authenticationHelper
     * @param ColumnNameResolver $columnNameResolver
     * @param $spreadsheetId
     * @param $sheetName
     */
    public function __construct(Client $client, AuthenticationHelper $authenticationHelper, ColumnNameResolver $columnNameResolver, $spreadsheetId, $sheetName)
    {
        $this->client = $client;
        $this->authenticationHelper = $authenticationHelper;
        $this->columnNameResolver = $columnNameResolver;
        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;
    }

    /**
     * @param bool $forceApprovalPrompt
     */
    public function authenticate($forceApprovalPrompt = false)
    {
        $this->authenticationHelper->authenticate($forceApprovalPrompt);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @see https://docs.sheetsu.com/?shell#read-all-data
     */
    public function read($limit = 0, $offset = 0)
    {
        $range = sprintf('%s!A1:%s%s', $this->sheetName, $this->getRightEdgeColumn(), self::MAX_ROW);
        $response = $this->client->getGoogleService()->spreadsheets_values->get($this->spreadsheetId, $range);
        $rows = $response->getValues();

        $keys = array_shift($rows);

        $rows = array_slice($rows, $offset, $limit ?: null);

        foreach ($rows as $i => $row) {
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

    public function create()
    {
    }

    public function update()
    {
    }

    public function delete()
    {
    }

    /**
     * @return string
     */
    private function getRightEdgeColumn()
    {
        $range = sprintf('%s!A1:%s1', $this->sheetName, self::MAX_COLUMN);
        $response = $this->client->getGoogleService()->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues();

        return $this->columnNameResolver->getName(count($values[0]));
    }
}
