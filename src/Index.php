<?php

namespace Index;

use Index\Model\TypeInterface;
use Index\Model\Entry;
use Index\Model\TypeProperty;
use Index\Source\SourceInterface;
use RuntimeException;
use TeamTNT\TNTSearch\TNTSearch;

class Index
{
    protected $store;
    protected $tnt;
    protected $tntIndex;
    protected $types = [];
    protected $sources = [];
    protected $renderer;
    protected $searcher;

    public function __construct($store)
    {
        $this->store = $store;
        $store->setIndex($this);

        $this->tnt = new TNTSearch();

        $this->tnt->loadConfig([
            'storage'   => '/Users/joostfaassen/git/linkorb/index/var/search/'
        ]);
        $this->tnt->selectIndex("entry.index");
        $this->tnt->fuzziness = true;
        //$this->tntIndex = $this->tnt->createIndex('entry.index');
    }

    public function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getRenderer()
    {
        if (!$this->renderer) {
            throw new RuntimeException("Renderer not defined for this index");
        }
        return $this->renderer;
    }

    public function updateSearchIndex(Entry $entry)
    {
        $tntIndex = $this->tnt->getIndex();
        $document = [
            'id' => $entry->getFqen(),
            'display_name' => $entry->getDisplayName()
        ];
        foreach ($entry->getProperties() as $p) {
            if ($p->getType()->hasFlag(TypeProperty::FLAG_SEARCH)) {
                $document[(string)$p->getType()->getName()] = (string)$p->getValue();
            }
        }
        $tntIndex->update($entry->getFqen(), $document);
    }

    public function search($query, $limit = 10)
    {
        $res = $this->tnt->search($query, $limit);
        return $res;
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
