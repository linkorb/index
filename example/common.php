<?php

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');

if (getenv('INDEX_DB_NAME')) {
    $username = getenv('INDEX_DB_USERNAME');
    $password = getenv('INDEX_DB_PASSWORD');
    $dbname = getenv('INDEX_DB_NAME');
    $host = getenv('INDEX_DB_HOST');
    $pdo = new PDO("mysql:dbname="  . $dbname . ";host=" . $host, $username, $password);
    $store = new \Index\Store\PdoStore($pdo);
} else {
    $store = new \Index\Store\MemoryStore();
}

if (getenv('INDEX_SEARCH_STORAGE_PATH')) {
    $searcher = new \Index\Searcher\TNTSearcher(getenv('INDEX_SEARCH_STORAGE_PATH'));
} else {
    $searcher = new \Index\Searcher\NullSearcher();
}
$index = new \Index\Index($store, $searcher);

// Load configuration such as sources, watches, etc
$configLoader = new \Index\Loader\ConfigLoader();
$configLoader->loadFile($index, __DIR__ . '/../config.yml');
