<?php

namespace Index\Store;

use PDO;
use Index\Model\Entry;
use Index\Model\EntryProperty;
use Index\Index;
use RuntimeException;

class PdoStore
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
            $this->loadEntryProperties($entry, $row['id']);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function getEntriesOfTypeByProperty($typeName, $propertyName, $propertyValue)
    {
        $stmt = $this->pdo->prepare(
            "SELECT e.* FROM entry AS e
            JOIN entry_property AS ep ON ep.entry_id = e.id
            WHERE type = :type
            AND ep.name=:name
            ORDER BY e.id"
        );
        $res = $stmt->execute(
            [
                'type' => $typeName,
                'name' => $propertyName
            ]
        );
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entries = [];
        foreach ($rows as $row) {
            $entry = $this->row2entry($row);
            $this->loadEntryProperties($entry, $row['id']);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function getEntriesByFolder($fqen)
    {
        if (!$fqen) {
            $folderId = 0;
        } else {
            $folderId = $this->getEntryIdByFqen($fqen);
        }
        $stmt = $this->pdo->prepare("SELECT * FROM entry WHERE parent_id = :folder_id ORDER BY id");
        $res = $stmt->execute([
            'folder_id' => $folderId
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $entries = [];
        foreach ($rows as $row) {
            $entry = $this->row2entry($row);
            $this->loadEntryProperties($entry, $row['id']);
            $entries[] = $entry;
        }
        return $entries;

    }

    public function getEntryById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entry WHERE id=:id");
        $res = $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $entry = $this->row2entry($row);

        $this->loadEntryProperties($entry, $id);

        return $entry;
    }

    private function loadEntryProperties(Entry $entry, $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entry_property WHERE entry_id=:id");
        $res = $stmt->execute(['id'=>$id]);
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
        $id = $this->getEntryIdByFqen($fqen);
        if (!$id) {
            return null;
        }
        return $this->getEntryById($id);
    }

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

    public function row2entry($row)
    {

        $typeName = $row['type'];
        $sourceName = $row['source'];
        $name = $row['name'];

        $type = $this->index->getTypeByName($typeName);
        $source = $this->index->getSource($sourceName);

        $properties = [];
        $entry = new Entry($type, $source, $name, $properties);
        $entry->setParentId($row['parent_id']);
        //$entry->setId($row['id']);
        //$entry->setName($row['name']);

        $type = $this->index->getTypeByName($row['type']);

        $entry->setType($type);

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
        return $entry;
    }

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

    public function persist(Entry $entry)
    {
        $id = $this->getEntryId($entry->getType()->getName(), $entry->getSource()->getName(), $entry->getName());
        if (!$id) {
            $stmt = $this->pdo->prepare("INSERT INTO entry (type, source, name, created_at) VALUES(:type, :source, :name, :created_at)");
            $res = $stmt->execute(
                [
                    'type' => $entry->getType()->getName(),
                    'source' => $entry->getSource()->getName(),
                    'name' => $entry->getName(),
                    'created_at' => time()
                ]
            );
            $id = $this->pdo->lastInsertId();
            if (!$id) {
                throw new RuntimeException("Failed to insert entry");
            }
        }
        // $id ensured

        // Update non identifier properties
        $stmt = $this->pdo->prepare("UPDATE entry SET display_name=:display_name, parent_id=:parent_id WHERE id=:id");
        $res = $stmt->execute(
            [
                'display_name' => $entry->getType()->getDisplayName($entry),
                'parent_id' => (int)$entry->getParentId(),
                'id' => $id
            ]
        );

        // Build property diff
        $stmt = $this->pdo->prepare("SELECT * FROM entry_property WHERE entry_id=:id");
        $res = $stmt->execute(
            [
                'id' => $id
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
        $this->applyPropertyDiffs($id, $propertyDiffs);
    }

    public function applyPropertyDiffs($id, $diffs)
    {
        // TODO: track changes in entry_event table
        foreach ($diffs as $diff) {
            switch ($diff['action']) {
                case 'insert':
                    $stmt = $this->pdo->prepare("INSERT INTO entry_property (entry_id, name, value) VALUES (:entry_id, :name, :value)");
                    $res = $stmt->execute(
                        [
                            'entry_id' => $id,
                            'name' => $diff['name'],
                            'value' => $diff['value']
                        ]
                    );
                    break;
                case 'update':
                    $stmt = $this->pdo->prepare("UPDATE entry_property SET value=:value WHERE entry_id=:entry_id AND name=:name");
                    $res = $stmt->execute(
                        [
                            'entry_id' => $id,
                            'name' => $diff['name'],
                            'value' => $diff['value']
                        ]
                    );
                    break;
                case 'delete':
                    $stmt = $this->pdo->prepare("DELETE FROM entry_property WHERE entry_id=:entry_id AND name=:name");
                    $res = $stmt->execute(
                        [
                            'entry_id' => $id,
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
