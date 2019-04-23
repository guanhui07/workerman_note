<?php

use Workerman\Worker;

require_once '../../vendor/autoload.php';

$worker = new Worker('tcp://0.0.0.0:8484');

$worker->onConnect = function($connection) {
    // $connection->protocol = 'Workerman\\Protocols\\Tcp';

    var_dump($connection->id);
};

$worker->onMessage = function($connection, $data) {
    // var_dump($_GET, $_POST);

    // send 时会自动调用$connection->protocol::encode()，打包数据后再发送
    $connection->send("hello");

    var_dump(count($connection->worker->connections));

    // 当一个客户端发来数据时，转发给当前进程所维护的其它所有客户端
    foreach($connection->worker->connections as $con) {
        $con->send('$connection->worker->connections');
    }
};

// 运行worker
Worker::runAll();

// 连接: telnet 127.0.0.1 8484
