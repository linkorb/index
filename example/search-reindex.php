<?php

require_once 'common.php';



$entries = $store->getEntries();
foreach($entries as $entry) {
    echo "Re-indexing " . $entry->getFqen() . "\n";
    $index->getSearcher()->updateEntry($entry);
}
