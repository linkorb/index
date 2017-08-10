<?php

namespace Index\Store;

use PDO;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Index;
use RuntimeException;

class PdoStore implements StoreInterface
{
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function setIndex(Index $index)
    {
        $this->index = $index;
    }

    public function getEntries()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entry ORDER BY id");
        $res = $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entries = [];
        foreach ($rows as $row) {
            $entry = $this->row2entry($row);
            $this->loadEntryProperties($entry);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function getEntriesOfTypeByProperty($typeName, $propertyName, $propertyValue)
    {
        $stmt = $this->pdo->prepare(
            "SELECT e.* FROM entry AS e
            JOIN entry_property AS ep ON ep.entry_fqen = e.fqen
            WHERE ep.name=:name
            AND ep.value=:value
            ORDER BY e.id"
        );
        $res = $stmt->execute(
            [
                'name' => $propertyName,
                'value' => $propertyValue
            ]
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entries = [];
        foreach ($rows as $row) {
            $entry = $this->row2entry($row);
            if ($entry->getType()->getName()==$typeName) {
                $this->loadEntryProperties($entry);
                $entries[] = $entry;
            }
        }
        return $entries;
    }

    public function getEntriesByParentFqen($fqen)
    {
        if (!$fqen) {
            $stmt = $this->pdo->prepare("SELECT * FROM entry WHERE parent_fqen IS NULL ORDER BY id");
            $res = $stmt->execute([]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM entry WHERE parent_fqen = :parent_fqen ORDER BY id");
            $res = $stmt->execute([
                'parent_fqen' => $fqen
            ]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $entries = [];
        foreach ($rows as $row) {
            $entry = $this->row2entry($row);
            $this->loadEntryProperties($entry);
            $entries[] = $entry;
        }
        return $entries;

    }

    private function loadEntryProperties(Entry $entry)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entry_property WHERE entry_fqen=:entry_fqen");
        $res = $stmt->execute(['entry_fqen'=>$entry->getFqen()]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $propertyType = $entry->getType()->getTypeProperty($row['name']);
            $property = new EntryProperty($propertyType, $row['value']);
            $entry->addProperty($property);
        }
        return $entry;
    }


    public function getEntryByFqen($fqen)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entry WHERE fqen=:fqen");
        $res = $stmt->execute(['fqen'=>$fqen]);
        if ($stmt->rowCount()==0) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $entry = $this->row2entry($row);

        $this->loadEntryProperties($entry);

        return $entry;
    }

    /*
    public function getEntryIdByFqen($fqen)
    {
        $part = explode(':', $fqen);
        if (count($part)!=3) {
            throw new RuntimeException("Invalid FQEN: " . $fqen);
        }
        $id = $this->getEntryId($part[0], $part[1], $part[2]);
        if (!$id) {
            return null;
        }
        return $id;
    }
    */

    public function row2entry($row)
    {
        $fqen = $row['fqen'];
        $part = explode(':', $fqen);
        if (count($part)!=3) {
            throw new RuntimeException("FQEN doesn't have 3 parts: " . $fqen);
        }
        $typeName = $part[0];
        $sourceName = $part[1];
        $name = $part[2];

        $type = $this->index->getTypeByName($typeName);
        $source = $this->index->getSource($sourceName);

        $properties = [];
        $entry = new Entry($this->index, $type, $source, $name, $properties);
        $entry->setParentFqen($row['parent_fqen']);
        //$entry->setId($row['id']);
        //$entry->setName($row['name']);

        /*
        $json = $row['properties'];
        $properties = json_decode($json, true);
        if ($properties) {
            foreach ($properties as $name => $value) {
                $property = new Property();
                $property->setName($name);
                $property->setValue($value);

                $entry->addProperty($property);
            }
        }
        */
        return $entry;
    }


    /*
    private function getEntryId($typeName, $sourceName, $entryName)
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


    public function persist(Entry $entry)
    {
        $e = $this->getEntryByFqen($entry->getFqen());
        if (!$e) {
            $stmt = $this->pdo->prepare("INSERT INTO entry (fqen, created_at) VALUES(:fqen, :created_at)");
            $res = $stmt->execute(
                [
                    'fqen' => $entry->getFqen(),
                    'created_at' => time()
                ]
            );
            $id = $this->pdo->lastInsertId();
            if (!$id) {
                throw new RuntimeException("Failed to insert entry");
            }
        }
        // record ensured

        // Update non identifier properties
        $stmt = $this->pdo->prepare("UPDATE entry SET display_name=:display_name, parent_fqen=:parent_fqen WHERE fqen=:fqen");
        $res = $stmt->execute(
            [
                'display_name' => $entry->getType()->getDisplayName($entry),
                'parent_fqen' => $entry->getParentFqen(),
                'fqen' => $entry->getFqen()
            ]
        );

        // Build property diff
        $stmt = $this->pdo->prepare("SELECT * FROM entry_property WHERE entry_fqen=:fqen");
        $res = $stmt->execute(
            [
                'fqen' => $entry->getFqen()
            ]
        );

        $storedProperties = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $storedProperties[$row['name']] = $row['value'];
        }

        $entryProperties = [];
        foreach ($entry->getProperties() as $property) {
            $entryProperties[$property->getType()->getName()] = $property->getValue();
        }
        $propertyDiffs = $this->diffProperties($entryProperties, $storedProperties);
        $this->applyPropertyDiffs($entry, $propertyDiffs);
        $this->index->updateSearchIndex($entry);
    }

    public function applyPropertyDiffs(Entry $entry, $diffs)
    {
        // TODO: track changes in entry_event table
        foreach ($diffs as $diff) {
            switch ($diff['action']) {
                case 'insert':
                    $stmt = $this->pdo->prepare("INSERT INTO entry_property (entry_fqen, name, value) VALUES (:entry_fqen, :name, :value)");
                    $res = $stmt->execute(
                        [
                            'entry_fqen' => $entry->getFqen(),
                            'name' => $diff['name'],
                            'value' => $diff['value']
                        ]
                    );
                    break;
                case 'update':
                    $stmt = $this->pdo->prepare("UPDATE entry_property SET value=:value WHERE entry_fqen=:entry_fqen AND name=:name");
                    $res = $stmt->execute(
                        [
                            'entry_fqen' => $entry->getFqen(),
                            'name' => $diff['name'],
                            'value' => $diff['value']
                        ]
                    );
                    break;
                case 'delete':
                    $stmt = $this->pdo->prepare("DELETE FROM entry_property WHERE entry_fqen=:entry_fqen AND name=:name");
                    $res = $stmt->execute(
                        [
                            'entry_fqen' => $entry->getFqen(),
                            'name' => $diff['name']
                        ]
                    );
                    break;
                default:
                    throw new RuntimeException("Unknown diff action: " . $diff['action']);
            }
        }

    }

    public function diffProperties($entryProperties, $storedProperties)
    {

        //
        // print_r($storedProperties);
        // print_r($entryProperties);

        $diffs = [];
        foreach ($entryProperties as $name => $value) {
            if (!isset($storedProperties[$name])) {
                $diffs[] = [
                    'action' => 'insert',
                    'name' => $name,
                    'value' => $value
                ];
            } else {
                if ($storedProperties[$name] != $value) {
                    $diffs[] = [
                        'action' => 'update',
                        'name' => $name,
                        'value' => $value
                    ];
                }
            }
        }

        foreach ($storedProperties as $name => $value) {
            if (!isset($entryProperties[$name])) {
                $diffs[] = [
                    'action' => 'delete',
                    'name' => $name,
                    'value' => null
                ];
            }
        }

        //print_r($diffs);
        return $diffs;
    }

}
