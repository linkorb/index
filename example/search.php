<?php

require_once 'common.php';

if (!isset($argv[1])) {
    exit("Please specify a search query\n");
}
$query = $argv[1];

echo "Searching top 5 for: $query\n";
$fqens = $index->getSearcher()->search($query, 5);
print_r($fqens);
echo "Results: " . count($fqens) . "\n";
$entries = [];
foreach ($fqens as $fqen) {
    $entry = $index->getStore()->getEntryByFqen($fqen);
    if ($entry) {
        echo $entry->getFqen() . " " . $entry->getDisplayName() . "\n";
    }
}
