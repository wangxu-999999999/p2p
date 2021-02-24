<?php

require_once 'library\Server.php';

set_time_limit(0);

try {
    $server = new \library\Server(8888);
    $server->start();
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
