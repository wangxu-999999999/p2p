<?php

set_time_limit(0);

require_once __DIR__ . '\vendor\autoload.php';

use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;

$ip = '127.0.0.1';
$port = 54229;
$ip2 = '127.0.0.1';
$port2 = 54227;
$udp_worker = new Worker("tcp://{$ip}:{$port}");
$udp_worker->onMessage = function($connection, $data){
    echo $data . PHP_EOL;
    sleep(1);
    $connection->send('1');
};
$udp_worker->onWorkerStart = function ($worker) use ($ip2, $port2) {

    $con = new AsyncTcpConnection("tcp://{$ip2}:{$port2}");
    $con->send('2');
    $con->onMessage = function ($con, $data) {
        echo $data . PHP_EOL;
        sleep(1);
        $con->send('2');
    };
    $con->connect();
};

Worker::runAll();
