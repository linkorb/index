<?php

namespace Index\Provider\GitHub;

use Index\Source\SourceInterface;

class GitHubSource implements SourceInterface
{
    protected $name;
    protected $client;


    public function __construct($name, $arguments)
    {
        $this->name = $name;

        $this->client = new \Github\Client();
        $this->client->authenticate($arguments['username'], $arguments['password'], \Github\Client::AUTH_HTTP_PASSWORD);
    }


    public function getClient()
    {
        return $this->client;
    }

    public function getName()
    {
        return $this->name;
    }

}
