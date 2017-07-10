<?php

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/../.env');


$username = getenv('INDEX_DB_USERNAME');
$password = getenv('INDEX_DB_PASSWORD');
$dbname = getenv('INDEX_DB_NAME');
$host = getenv('INDEX_DB_HOST');

$pdo = new PDO("mysql:dbname="  . $dbname . ";host=" . $host, $username, $password);

$store = new \Index\Store\PdoStore($pdo);

$index = new \Index\Index($store);

// Load configuration such as sources, watches, etc
$configLoader = new \Index\Loader\ConfigLoader();
$configLoader->loadFile($index, __DIR__ . '/../config.yml');
