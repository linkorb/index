<?php

namespace Index\Model;

class TypeProperty implements TypePropertyInterface
{
    const FLAG_IDENTIFIER = 1;
    const FLAG_REMOTE = 2;
    const FLAG_EDITABLE = 4;
    const FLAG_MULTIPLE = 8;
    const FLAG_REQUIRED = 16;
    const FLAG_SEARCH = 32;
    const FLAG_HIDDEN = 64;

    const TYPE_INTEGER = 1;
    const TYPE_STRING = 2;
    const TYPE_DATE = 3;
    const TYPE_BOOLEAN = 4;
    const TYPE_FQEN = 5;

    protected $name;
    protected $type;
    protected $flags;

    public function __construct($name, $type, $flags = 0)
    {
        $this->name = $name;
        $this->type = $type;
        $this->flags = $flags;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function hasFlag($flag)
    {
        return (($this->flags & $flag) == $flag);
    }

    public function presentFlags()
    {
        $flagNames = [];
        if ($this->hasFlag(self::FLAG_IDENTIFIER)) {
            $flagNames[] = 'IDENTIFIER';
        }
        if ($this->hasFlag(self::FLAG_REMOTE)) {
            $flagNames[] = 'REMOTE';
        }
        if ($this->hasFlag(self::FLAG_EDITABLE)) {
            $flagNames[] = 'EDITABLE';
        }
        if ($this->hasFlag(self::FLAG_MULTIPLE)) {
            $flagNames[] = 'MULTIPLE';
        }
        if ($this->hasFlag(self::FLAG_REQUIRED)) {
            $flagNames[] = 'REQUIRED';
        }

        $o = '';
        foreach ($flagNames as $flagName) {
            $o .= '#' . $flagName . ' ';
        }
        return trim($o);


    }
}
