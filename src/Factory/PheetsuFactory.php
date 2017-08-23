<?php

namespace Ttskch\Pheetsu\Factory;

use Ttskch\GoogleSheetsApi\Authenticator;
use Ttskch\GoogleSheetsApi\Factory\ApiClientFactory;
use Ttskch\GoogleSheetsApi\Factory\GoogleClientFactory;
use Ttskch\Pheetsu\AuthenticationHelper;
use Ttskch\Pheetsu\Pheetsu;

class PheetsuFactory
{
    /**
     * @param $clientId
     * @param $clientSecret
     * @param $redirectUri
     * @param $javascriptOrigin
     * @param $spreadsheetId
     * @param $sheetName
     * @return Pheetsu
     */
    static public function create($clientId, $clientSecret, $redirectUri, $javascriptOrigin, $spreadsheetId, $sheetName)
    {
        $googleClient = GoogleClientFactory::create($clientId, $clientSecret, $redirectUri, $javascriptOrigin);

        $apiClient = ApiClientFactory::create($googleClient);

        $authenticator = new Authenticator($googleClient);
        $authenticationHelper = new AuthenticationHelper($authenticator);

        $pheetsu = new Pheetsu($apiClient, $authenticationHelper, $spreadsheetId, $sheetName);

        return $pheetsu;
    }
}
