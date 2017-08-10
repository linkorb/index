<?php

namespace Index\Model;

use Index\Source\SourceInterface;
use Index\Index;
use Doctrine\Common\Inflector\Inflector;

class Entry
{
    protected $type;
    protected $parentFqen;
    protected $name;
    protected $source;
    protected $properties = [];


    public function __construct(Index $index, TypeInterface $type, SourceInterface $source, $name, $properties = [])
    {
        $this->index = $index;
        $this->type = $type;
        $this->source = $source;
        $this->name = $name;
        foreach ($properties as $property) {
            $this->addProperty($property);
        }
        // Santity check: do entry identifiers match type's identifiers?
    }

    public function setParentFqen($fqen)
    {
        $this->parentFqen = $fqen;
    }

    public function getParentFqen()
    {
        return $this->parentFqen;
    }

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

    public function getName()
    {
        return $this->name;
    }

    /*
    public function setType(TypeInterface $type)
    {
        $this->type = $type;
    }*/
    public function getType()
    {
        return $this->type;
    }

    public function setSource(SourceInterface $source)
    {
        $this->source = $source;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function addProperty(EntryProperty $property)
    {
        $this->properties[] = $property;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function __call($name, $args)
    {
        switch (substr($name, 0, 3)) {
            case 'get':
                return $this->getPropertyValue(Inflector::tableize(substr($name, 3)));
                break;
            case 'set':
                break;
            default:
                throw new MemberAccessException('Method ' . $name . ' does not exists on this entry');
        }

    }

    public function getPropertyValue($name)
    {
        // check if property is single/multiple?
        $pt = $this->getType()->getTypeProperty($name);
        if (!$pt) {
            throw new RuntimeException("Entry of type doesn't have property " . $name);
        }
        if ($pt->hasFlag(TypeProperty::FLAG_MULTIPLE)) {
            $res = [];
            foreach ($this->properties as $property) {
                if ($property->getType()->getName()==$name) {
                    $v = $property->getValue();
                    if ($pt->getType()=='entry') {
                        $v = $this->index->getStore()->getEntryByFqen($v);
                    }
                    $res[] = $v;
                }
            }
            return $res;
        } else {
            foreach ($this->properties as $property) {
                if ($property->getType()->getName()==$name) {
                    $v = $property->getValue();
                    if ($pt->getType()=='entry') {
                        $v = $this->index->getStore()->getEntryByFqen($v);
                    }
                    return $v;
                }
            }
            return null;
        }
    }

}
