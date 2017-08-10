<?php

namespace Index\Source;

use Google_Service_Drive;
use Google_Client;

class GoogleDriveSource implements SourceInterface
{
    protected $name;
    protected $client;
    protected $service;
    protected $appName = 'index';
    protected $clientSecretPath;


    public function __construct($name, $arguments)
    {
        $this->name = $name;
        $this->clientSecretPath = $arguments['client_secret_path'];
        $this->credentialsPath = $arguments['credentials_path'];

        $client = $this->instantiateClient();
        $this->service = new Google_Service_Drive($client);

        $this->client = $client;
    }


    private function instantiateClient()
    {
        $client = new Google_Client();
        $client->setApplicationName($this->appName);
        $client->setScopes([Google_Service_Drive::DRIVE]);
        $client->setAuthConfig($this->clientSecretPath);
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = $this->expandHomeDirectory($this->credentialsPath);
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($this->credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            // Store the credentials to disk.
            if(!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
     * Expands the home directory alias '~' to the full path.
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    private function expandHomeDirectory($path) {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getService()
    {
        return $this->service;
    }

    public function getName()
    {
        return $this->name;
    }

}
