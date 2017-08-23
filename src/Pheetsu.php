<?php

namespace Ttskch\Pheetsu;

use Ttskch\GoogleSheetsApi\Client;

class Pheetsu
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var AuthenticationHelper
     */
    private $authenticationHelper;

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
     * @param $spreadsheetId
     * @param $sheetName
     */
    public function __construct(Client $client, AuthenticationHelper $authenticationHelper, $spreadsheetId, $sheetName)
    {
        $this->client = $client;
        $this->authenticationHelper = $authenticationHelper;
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

    public function read($range)
    {
        $response = $this->client->getGoogleService()->spreadsheets_values->get($this->spreadsheetId, sprintf('%s!%s', $this->sheetName, $range));
        $values = $response->getValues();

        var_dump($values);
        exit;
    }

    public function search()
    {
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
}
