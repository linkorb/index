<?php

namespace Index\Model;

class TypeTab implements TypePropertyInterface
{
    const TYPE_TEMPLATE = 1;
    const TYPE_IFRAME = 2;
    const TYPE_RESPONSE = 3;
    const TYPE_ENTRY_LIST = 4;

    protected $name;
    protected $label;
    protected $type;

    public function __construct($name, $label, $type)
    {
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getType()
    {
        return $this->type;
    }
}
