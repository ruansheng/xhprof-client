<?php
$file = dirname(__FILE__) . '/../vendor/autoload.php';
include_once $file;

use Xhprof\Client\Client;

Client::info();