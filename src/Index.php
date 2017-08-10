<?php

namespace Index;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Source\SourceInterface;
use Index\Store\StoreInterface;
use Index\Model\ProviderInterface;
use Index\Searcher\SearcherInterface;
use RuntimeException;


class Index
{
    protected $store;
    protected $types = [];
    protected $sources = [];
    protected $providers = [];
    protected $renderer;
    protected $searcher;

    public function __construct(StoreInterface $store, SearcherInterface $searcher)
    {
        $this->store = $store;
        $this->searcher = $searcher;
        $store->setIndex($this);
    }

    public function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getSearcher()
    {
        return $this->searcher;
    }

    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getRenderer()
    {
        if (!$this->renderer) {
            throw new RuntimeException("Renderer not defined for this index");
        }
        return $this->renderer;
    }

    public function getStore()
    {
        return $this->store;
    }

    public function addType(TypeInterface $type)
    {
        if (isset($this->types[$type->getName()])) {
            throw new RuntimeException("Type already registered: " . $type->getName());
        }
        $this->types[$type->getName()] = $type;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getTypeByName($name)
    {
        if (!isset($this->types[$name])) {
            throw new RuntimeException("Unknown type: " . $name);
        }
        return $this->types[$name];
    }

    public function hasType($name)
    {
        return isset($this->types[$name]);
    }


    public function findTypesByUrl($url)
    {
        $types = [];
        foreach ($this->getTypes() as $type) {
            if ($type->supportsUrl($url)) {
                $types[] = $type;
            }
        }
        return $types;
    }

    public function addSource(SourceInterface $source)
    {
        $this->sources[$source->getName()] = $source;
    }

    public function getSource($name)
    {
        if (!isset($this->sources[$name])) {
            throw new RuntimeException("Unknown source: " . $name);
        }
        return $this->sources[$name];
    }

    public function getSources()
    {
        return $this->sources;
    }
}
