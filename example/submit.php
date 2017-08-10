<?php

require_once 'common.php';

if ($argc<2) {
    throw new RuntimeException("Usage: submit [url] [type-name]");
}
$typeName = null;

$url = $argv[1];
$sourceName = null;
if (isset($argv[2])) {
    $typeName = $argv[2];

    if (isset($argv[3])) {
        $sourceName = $argv[3];
    }

}

$types = $index->findTypesByUrl($url);
if (count($types)==0) {
    echo "No types registered that support this url\n";
    exit(-1);
}
echo "Supporting types:\n";
foreach ($types as $type) {
    echo " * " . $type->getName() . "\n";
}
if ($typeName) {
    $type = $index->getTypeByName($typeName);
    if (!$sourceName) {
        $sourceName = $type->getDefaultSourceName();
    }
    $source = $index->getSource($sourceName);


    $properties = $type->urlToProperties($url);
    $name = $type->getNameFromProperties($properties);

    $entry = new \Index\Model\Entry($index, $type, $source, $name, $properties);
    echo "FQEN: " . $entry->getFqen() . " (NAME: " . $entry->getName() . ")\n";
    $properties = $type->fetchRemoteProperties($source, $properties);
    foreach ($properties as $property) {
        $entry->addProperty($property);
    }

    foreach ($entry->getProperties() as $property) {
        echo " - " . $property->getType()->getName() . ' = ' . $property->getValue() . " (" . $property->getType()->presentFlags() . ")\n";
    }

    $store->persist($entry);
    //print_r($entry);
}
//print_r($types);
