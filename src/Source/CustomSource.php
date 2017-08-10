<?php

namespace Index\Source;

class CustomSource implements SourceInterface
{
    protected $name;
    protected $client;


    public function __construct($name, $arguments)
    {
        $this->name = $name;

    }

    public function getDisplayName()
    {
        return 'todo';
    }

    public function getName()
    {
        return $this->name;
    }

}
