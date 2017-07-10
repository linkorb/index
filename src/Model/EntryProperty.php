<?php

namespace Index\Model;

class EntryProperty
{
    protected $value;
    protected $type;

    public function __construct(TypePropertyInterface $type, $value = null)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
    public function getValue()
    {
        return $this->value;
    }

}
