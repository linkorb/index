<?php

require_once 'common.php';

$store = $index->getStore();
$entries = $store->getEntries();

foreach ($entries as $entry) {
    echo $entry->getFqen() . "\n";
    foreach ($entry->getProperties() as $p) {
        //echo "  - " . $p->getType()->getName() . '=' . $p->getValue() . "\n";
    }
}
