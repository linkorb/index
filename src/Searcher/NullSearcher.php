<?php

namespace Index\Searcher;

use Index\Model\Entry;

class NullSearcher implements SearcherInterface
{
    public function __construct()
    {

    }

    public function updateEntry(Entry $entry)
    {
        return; // noop
    }

    public function search($query)
    {
        return []; // noop
    }
}
