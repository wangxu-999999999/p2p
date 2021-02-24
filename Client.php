<?php

require_once 'library\Client.php';

set_time_limit(0);

try {
    $server = new \library\Client('127.0.0.1', 8888);
    $server->start();
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
