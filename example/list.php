<?php

require_once 'common.php';

$store = $index->getStore();
$entries = $store->getEntries();


print_r($entries);
