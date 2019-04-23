<?php

use Workerman\Worker;

require_once __DIR__ . '/../../vendor/autoload.php';

// 创建一个Worker监听2347端口，不使用任何应用层协议
$tcp_worker = new Worker("tcp://0.0.0.0:2347");

// 启动4个进程对外提供服务
$tcp_worker->count = 4;

// 当客户端发来数据时
$tcp_worker->onMessage = function ($connection, $data) {
    var_dump("收到数据: " . $data);

    // 向客户端发送hello $data
    $connection->send('hello ' . $data);
};

// 运行worker
Worker::runAll();

/*
说明: 实例三、直接使用TCP传输数据

服务端: php tcp_test.php start

客户端:
    telnet 127.0.0.1 2347
    Trying 127.0.0.1...
    Connected to 127.0.0.1.
    Escape character is '^]'.
    tom
    hello tom
*/
