<?php

namespace Index\Source;

class NullSource implements SourceInterface
{
    public function __construct($name, $arguments = [])
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return 'null';
    }
}
