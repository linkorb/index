<?php

namespace Index\Searcher;

use Index\Model\Entry;
use Index\Model\TypeProperty;
use TeamTNT\TNTSearch\TNTSearch;

class TNTSearcher implements SearcherInterface
{

    protected $tnt;

    public function __construct($storagePath, $filename = 'entry.index')
    {
        $this->tnt = new TNTSearch();

        $this->tnt->loadConfig([
            'storage'   => $storagePath
        ]);
        $this->tnt->selectIndex($filename);
        $this->tnt->fuzziness = true;
        //$this->tntIndex = $this->tnt->createIndex('entry.index');
    }

    public function updateEntry(Entry $entry)
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

        $fqens = [];
        foreach ($res['ids'] as $i=>$fqen) {
            if ($fqen) {
                $fqens[] = $fqen;
            }
        }
        return $fqens;
    }

}
