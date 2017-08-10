<?php

namespace Index\Provider\Airtable;

use Index\Source\SourceInterface;
use GuzzleHttp\Client as GuzzleClient;

class AirtableSource implements SourceInterface
{
    protected $name;
    protected $apikey;
    protected $appkey;

    public function __construct($name, $arguments)
    {
        $this->name = $name;
        $this->apikey = $arguments['apikey'];
        $this->appkey = $arguments['appkey'];
    }


    public function getApiKey()
    {
        return $this->apikey;
    }

    public function getAppKey()
    {
        return $this->appkey;
    }

    public function getName()
    {
        return $this->name;
    }

    public function get($uri)
    {
        $headers = [
            "Authorization" => "Bearer " . $this->apikey
        ];
        $this->httpClient = new GuzzleClient(
            [
                'headers' => $headers
            ]
        );

        $url = 'https://api.airtable.com/v0/' . $this->appkey . '/';
        $url .= $uri;
        $res = $this->httpClient->get(
            $url,
            [
                'headers' => $headers,
            ]
        );
        if ($res->getStatusCode() != 200) {
            throw new RuntimeException("Unexpected statuscode: " . $res->getStatusCode());
        }
        $data = json_decode($res->getBody(), true);
        return $data;
    }

}
