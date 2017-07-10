<?php

namespace Index\Model;

class CustomType extends BaseType
{
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function addTypeProperty(TypePropertyInterface $typeProperty)
    {
        $this->typeProperties[$typeProperty->getName()] = $typeProperty;
    }

    public function supportsUrl($url)
    {
        return false; // no automatic urls supported
    }

    public function getDisplayName(Entry $entry)
    {
        return $entry->getName();
    }
}
