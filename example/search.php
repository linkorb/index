<?php

require_once 'common.php';

if (!isset($argv[1])) {
    exit("Please specify a search query\n");
}
$query = $argv[1];

echo "Searching top 5 for: $query\n";
$res = $index->search($query, 5);
print_r($res);
echo "Results: " . count($res['ids']) . "\n";
$entries = [];
foreach ($res['ids'] as $fqen) {
    if ($fqen) {
        $entry = $index->getStore()->getEntryByFqen($fqen);
        if ($entry) {
            echo $entry->getFqen() . " " . $entry->getDisplayName() . "\n";
        }
    }
}
