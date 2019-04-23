<?php

require_once 'vendor/autoload.php';

use Workerman\Worker;
use PHPSocketIO\SocketIO;

// 创建socket.io服务端，监听2021端口
$io = new SocketIO(3120);

// 监听一个http端口，通过http协议访问这个端口可以向所有客户端推送数据(url类似http://ip:9191?msg=xxxx)
$io->on('workerStart', function () use ($io) {
    $inner_http_worker = new Worker('http://0.0.0.0:9191');

    $inner_http_worker->onMessage = function ($http_connection, $data) use ($io) {
        if (!isset($_GET['msg'])) {
            return $http_connection->send('fail, $_GET["msg"] not found');
        }

        $io->emit('chat message', $_GET['msg']);

        $http_connection->send('ok');
    };

    $inner_http_worker->listen();
});

// 当有客户端连接时打印一行文字
$io->on('connection', function ($socket) use ($io) {
    echo "new connection coming {$socket->conn->remoteAddress} \n";

    // 定义chat message事件回调函数
    $socket->on('chat message', function ($msgFromClient) use ($io) {
        print_r($msgFromClient);
        // 触发所有客户端定义的chat message from server事件
        $io->emit('chat message from server', $msgFromClient);
    });
});

Worker::runAll();
