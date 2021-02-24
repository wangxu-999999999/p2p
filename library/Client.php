<?php

namespace library;

require_once __DIR__ . '\..\vendor\autoload.php';
require_once 'Base.php';

use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;

class Client extends Base {

    private $worker;
    private $localAddr;

    /**
     * Client constructor.
     * @param string $ip
     * @param int $port
     */
    public function __construct($ip, $port)
    {
        $this->worker = new Worker();
        $this->worker->onWorkerStart = function ($worker) use ($ip, $port) {

            $con = new AsyncTcpConnection("ws://{$ip}:{$port}");

            $con->onConnect = function ($con) {
                echo '连接成功' . PHP_EOL;
                $this->localAddr = $con->getLocalIp() . ':' . $con->getLocalPort();
                $this->join($con);
            };

            $con->onMessage = function ($con, $data) {
                if ($data = $this->analyse($data)) {
                    switch ($data['action']) {
                        case 'join':
                            if ($data['code'] == 1) {
                                echo '加入成功' . PHP_EOL;
                                $this->getList($con);
                            } else {
                                echo '加入失败' . PHP_EOL;
                            }
                            break;
                        case 'getList':
                            if ($data['code'] == 1) {
                                $list = $data['data']['list'];
                                if (!$list) {
                                    sleep(5);
                                    $this->getList($con);
                                } else {
                                    echo '列表获取成功' . PHP_EOL;
                                    $node = reset($list);
                                    echo $this->localAddr . PHP_EOL;
                                    echo $node;
                                }
                            } else {
                                echo '列表获取失败' . PHP_EOL;
                            }
                            break;
                        default:
                            break;
                    }
                }
            };

            $con->connect();
        };
    }

    /**
     * @author WX
     * @datetime 2021/2/22 9:24
     */
    public function start()
    {
        Worker::runAll();
    }

    public function join($con)
    {
        return $this->send($con, json_encode(['action' => 'join', 'id' => time()]));
    }

    public function getList($con)
    {
        return $this->send($con, json_encode(['action' => 'getList']));
    }

    /**
     * @param $con
     * @param $data
     * @return mixed
     * @author WX
     * @datetime 2021/2/24 9:51
     */
    public function send($con, $data)
    {
        return $con->send($data);
    }
}
