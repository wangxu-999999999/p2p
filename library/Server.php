<?php

namespace library;

require_once __DIR__ . '\..\vendor\autoload.php';
require_once 'Base.php';

use Workerman\Worker;

class Server extends Base {

    private $worker;
    private $list = [];

    /**
     * Server constructor.
     * @param int $port
     * @param string $ip
     * @param int $count
     */
    public function __construct($port, $ip = '0.0.0.0', $count = 4)
    {
        $this->worker = new Worker("websocket://{$ip}:{$port}");
        $this->worker->count = $count;

        $this->worker->onConnect = function ($connection) {
            echo '新连接：'  . $connection->getRemoteAddress() . PHP_EOL;
        };

        $this->worker->onMessage = function ($connection, $data) {
            if ($data = $this->analyse($data)) {
                switch ($data['action']) {
                    case 'join':
                        if ($this->join($connection, $data['id'])) {
                            $this->success($connection, 'join');
                        } else {
                            $this->error($connection, 'join');
                        }
                        break;
                    case 'getList':
                        $list = $this->getList($connection);
                        $this->success($connection, 'getList', '获取成功', ['list' => $list]);
                        break;
                    default:
                        break;
                }
            }
        };

        $this->worker->onClose = function ($connection) {
            $addr = $connection->getRemoteAddress();
            echo '连接关闭：'  . $addr . PHP_EOL;
            $this->list = array_diff($this->list, [$addr]);
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

    /**
     * 加入
     * @param $connection
     * @param $id
     * @return bool
     * @author WX
     * @datetime 2021/2/22 11:05
     */
    private function join($connection, $id)
    {
        if ($id) {
            $this->list[$id] = $connection->getRemoteAddress();
            return true;
        }
        return false;
    }

    /**
     * 列表
     * @param $connection
     * @return array
     * @author WX
     * @datetime 2021/2/22 11:15
     */
    private function getList($connection)
    {
        $addr = $connection->getRemoteAddress();
        return array_diff($this->list, [$addr]);
    }

    private function success($connection, $action, $msg = '操作成功', $data = [], $code = 1)
    {
        $this->send($connection, json_encode([
            'action' => $action,
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]));
    }

    private function error($connection, $action, $msg = '操作失败', $data = [], $code = 0)
    {
        $this->send($connection, json_encode([
            'action' => $action,
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]));
    }

    /**
     * @param $connection
     * @param $data
     * @return mixed
     * @author WX
     * @datetime 2021/2/23 17:09
     */
    private function send($connection, $data)
    {
        return $connection->send($data);
    }
}
