<?php

namespace Index\Store;

use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Index;
use RuntimeException;

class MemoryStore
{
    protected $entries = [];

    public function __construct()
    {
    }

    public function setIndex(Index $index)
    {
        $this->index = $index;
    }

    public function getEntries()
    {
        return $this->entries;
    }

    public function has($fqen)
    {
        return isset($this->entries[$fqen]);
    }


    public function getEntryByFqen($fqen)
    {
        return $this->entries[$fqen];
    }

    /*
    public function getEntryId($typeName, $sourceName, $entryName)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM entry WHERE type=:type AND source=:source and name=:name");
        $res = $stmt->execute(
            [
                'type' => $typeName,
                'source' => $sourceName,
                'name' => $entryName
            ]
        );
        if ($stmt->rowCount()>1) {
            throw new RuntimeException("More than one entry with same FQEN");
        }
        if ($stmt->rowCount()<1) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['id'];
    }
    */

    public function getEntriesByTypeName($typeName)
    {
        $res = [];
        foreach ($this->entries as $entry) {
            if ($entry->getType()->getName()==$typeName) {
                $res[] = $entry;
            }
        }
        return $res;
    }

    public function persist(Entry $entry)
    {

        $this->entries[$entry->getFqen()] = $entry;
        //$this->index->updateSearchIndex($id, $entry);
    }

}
