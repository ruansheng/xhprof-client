<?php
$file = dirname(__FILE__) . '/../vendor/autoload.php';
include_once $file;

use Xhprof\Client\Client;

$client = new Client();
$client->setCollectRate();
$client->setProjectId('huzhu');
$client->setRedisAddres();
$client->setRedisKeyInfo('xhprof-data');
$client->collection();
