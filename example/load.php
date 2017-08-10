<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Instantiate a volatile memory store
$store = new \Index\Store\MemoryStore();

// Instantiate the index
$index = new \Index\Index($store);

$l = new \Index\Loader\CustomTypeLoader();
$l->loadfile($index, __DIR__ . '/example-types.yml');

$source = new \Index\Source\NullSource('local');

$l = new \Index\Loader\YamlEntryLoader();
$l->loadfile($index, $source, __DIR__ . '/example-entries.yml');

// List all entries and their properties
foreach ($index->getStore()->getEntries() as $e) {
    echo $e->getFqen() . "\n";
    foreach ($e->getProperties() as $p) {
        echo "  - " . $p->getType()->getName() . ' = ' . $p->getValue() . "\n";
    }
}

$alice = $index->getStore()->getEntryByFqen('person:local:dave');
echo $alice->getFqen() . " `" . $alice->getName() . "`!\n";
echo $alice->getPropertyValue('firstname') . "\n";

// Support using magic getters
$manager = $alice->getManager();
if ($manager) {
    echo "Manager: " . $manager->getFirstname() . "\n";
}
echo "Likes " . count($alice->getColors()) . " colors\n";
foreach ($alice->getColors() as $color) {
    echo " - $color\n";
}

$devs = $index->getStore()->getEntryByFqen('team:local:developers');
echo $devs->getFqen() . " `" . $devs->getName() . "`!\n";
$members = $devs->getPropertyValue('members');
foreach ($devs->getMembers() as $member) {
    echo " * " . $member->getFirstname()  . "\n";
}
//print_r($members);

$teams = $index->getStore()->getEntriesByTypeName('team');
foreach ($teams as $team) {
    echo $team->getName() . "\n";
}

// Load configuration such as sources, watches, etc
//$configLoader = new \Index\Loader\ConfigLoader();
//$configLoader->loadFile($index, __DIR__ . '/../config.yml');
