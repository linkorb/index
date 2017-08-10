<?php

namespace Index\Loader;

use Index\Index;
use Index\Model\CustomType;
use Index\Source\SourceInterface;
use Index\Model\EntryProperty;
use Index\Model\Entry;
use RuntimeException;
use Doctrine\Common\Inflector\Inflector;

abstract class ArrayEntryLoader
{
    public function loadData(Index $index, SourceInterface $source, $data)
    {
        $store = $index->getStore();
        foreach ($data as $typeName => $entitiesData) {
            if ($typeName[0]!='_') {
                if (!$index->hasType($typeName)) {
                    throw new RuntimeException("Can't load entity of undefined type: " . $typeName);
                }
                $type = $index->getTypeByName($typeName);

                foreach ($entitiesData as $entryName => $entryData) {
                    $entry = new Entry($index, $type, $source, $entryName);
                    if ($entryData) {
                        foreach ($entryData as $k => $v) {
                            // process references (@)
                            if (is_array($v)) {
                                $vProcessed = [];
                                foreach ($v as $k2=>$v2) {
                                    $pt = $type->getTypeProperty($k);
                                    if ($pt->getType()=='reference') {
                                        $linkKey = substr($v2,1);
                                        if (!$store->has($linkKey)) {
                                            throw new RuntimeException("Linking to unknown key: " . $linkKey);
                                        }
                                        $v2 = $store->getEntryByFqen($linkKey);
                                    }
                                    $p = new EntryProperty($pt, $v2);
                                    $entry->addProperty($p);
                                }
                            } else {
                                $pt = $type->getTypeProperty($k);
                                $p = new EntryProperty($pt, $v);
                                $entry->addProperty($p);
                            }
                        }
                    }
                    $store->persist($entry);
                }
            }
        }

        return $index;
    }
}
