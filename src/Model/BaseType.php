<?php

namespace Index\Model;

use Index\Source\SourceInterface;
use RuntimeException;

abstract class BaseType implements TypeInterface
{
    protected $name;
    protected $urlPattern = null;

    protected $index;

    public function __construct($index)
    {
        $this->index = $index;
        $this->configure();
    }

    protected function configure()
    {
        return; // noop
    }

    public function render($filename, $data = [])
    {
        return $this->index->getRenderer()->render($filename, $data);
    }

    public function getName()
    {
        return $this->name;
    }

    protected $icon;

    public function getIcon()
    {
        return $this->icon;
    }

    protected $typeProperties = [];

    public function getTypeProperties()
    {
        return $this->typeProperties;
    }

    public function getTypeProperty($name)
    {
        if (!isset($this->typeProperties[$name])) {
            throw new RuntimeException("This type (" . $this->name . ") does not have a property `" . $name . "`");
        }
        return $this->typeProperties[$name];
    }

    protected function addTypeProperty(TypePropertyInterface $typeProperty)
    {
        $this->typeProperties[$typeProperty->getName()] = $typeProperty;
    }

    public function defineProperty($name, $type, $flags)
    {
        $property = new TypeProperty($name, $type, $flags);
        $this->addTypeProperty($property);
        return $this;
    }

    protected $typeTabs = [];

    public function getTypeTabs()
    {
        return $this->typeTabs;
    }

    protected function addTypeTab(TypeTab $typeTab)
    {
        $this->typeTabs[$typeTab->getName()] = $typeTab;
    }

    public function defineTab($name, $label, $type)
    {
        $tab = new TypeTab($name, $label, $type);
        $this->addTypeTab($tab);
        return $this;
    }

    public function supportsUrl($url)
    {
        if (!$this->urlPattern) {
            return false;
        }

        if (preg_match($this->urlPattern, $url, $matches)) {
            return true;
        }
        return false;

    }

    public function urlToProperties($url)
    {
        if (!$this->supportsUrl($url)) {
            throw new RuntimeException("This type does not support url: " . $url);
        }


        $properties = [];
        preg_match($this->urlPattern, $url, $matches);
        foreach ($this->identifiers as $name) {
            $tp = $this->getTypeProperty($name);
            $value = $matches[$name];
            $ep = new EntryProperty($tp, $value);
            $properties[$name] = $ep;
        }
        return $properties;
    }


    public function nameToProperties($name)
    {
        $properties = [];
        $part = explode(',', $name);
        $i = 0;
        foreach ($this->typeProperties as $typeProperty) {
            if ($typeProperty->hasFlag(TypeProperty::FLAG_IDENTIFIER)) {
                $name = $typeProperty->getName();
                $value = $part[$i];
                $ep = new EntryProperty($typeProperty, $value);
                $properties[$name] = $ep;
                $i++;
            }
        }
        return $properties;
    }


    public function getNameFromProperties($properties)
    {
        $name = '';
        foreach ($properties as $property) {
            if ($property->getType()->hasFlag(TypeProperty::FLAG_IDENTIFIER)) {
                $value = trim($property->getValue());
                // TODO: check if url safe? no seperators, etc
                $name .= $value . ',';
            }
        }
        return trim($name, ' ,');
    }

    public function fetchRemoteProperties(SourceInterface $source, $identifiers = [])
    {
        return [];
    }

    public function getDefaultSourceName()
    {
        return $this->defaultSourceName;
    }
}
