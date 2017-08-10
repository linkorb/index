<?php

require_once 'common.php';



$entries = $store->getEntries();
foreach($entries as $entry) {
    $id = $index->getStore()->getEntryIdByFqen($entry->getFqen());
    echo "Re-indexing " . $entry->getFqen() . " ($id)\n";
    $index->updateSearchIndex($id, $entry);
}
