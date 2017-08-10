<?php

namespace Index\Provider\Doxedo;

use Index\Source\SourceInterface;

class DoxedoSource implements SourceInterface
{
    protected $name;
    protected $client;


    public function __construct($name, $arguments)
    {
        $this->name = $name;

        $this->client = new \Monica\Client\Client(
            $arguments['username'],
            $arguments['password'],
            'http://www.doxedo.com'
        );
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
