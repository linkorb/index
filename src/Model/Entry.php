<?php

namespace Index\Model;

class Entry
{

    public function __construct(TypeInterface $type, SourceInterface $source, $name, $properties = [])
    {
        $this->type = $type;
        $this->source = $source;
        $this->name = $name;
        foreach ($properties as $property) {
            $this->addProperty($property);
        }
        // Santity check: do entry identifiers match type's identifiers?
    }

    protected $parentId = 0;

    public function setParentId($id)
    {
        $this->parentId = $id;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    /*
    protected $id;
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getId()
    {
        return $this->id;
    }
    */

    public function getDisplayName()
    {
        return $this->getType()->getDisplayName($this);
    }

    /**
     * example: github-pull-request:accountname:linkorb.index.20'
     */
    public function getFqen()
    {
        $fqen = $this->type->getName() . ':';
        $fqen .= $this->getSource()->getName() . ':';
        $fqen .= $this->getName();
        return $fqen;
    }

    protected $name;
    public function getName()
    {
        return $this->name;
    }

    protected $type;
    public function setType(TypeInterface $type)
    {
        $this->type = $type;
    }
    public function getType()
    {
        return $this->type;
    }

    protected $source;
    public function setSource(SourceInterface $source)
    {
        $this->source = $source;
    }

    public function getSource()
    {
        return $this->source;
    }


    protected $properties = [];

    public function addProperty(EntryProperty $property)
    {
        $this->properties[] = $property;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getPropertyValue($name)
    {
        // check if property is single/multiple?

        foreach ($this->properties as $property) {
            if ($property->getType()->getName()==$name) {
                return $property->getValue();
            }
        }
    }

}
