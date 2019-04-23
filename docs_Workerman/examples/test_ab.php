<?php

# 压力测试 http://www.workerman.net/bench
# TODO: 看不懂测试结果啊

use Workerman\Worker;

require_once './vendor/autoload.php';

$worker = new Worker('tcp://0.0.0.0:1234');

// 进程数配置成cpu核数-1，保留一个cpu给ab进程
$worker->count =3;

$worker->onMessage = function ($connection, $data) {
    $connection->send("HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: workerman\1.1.4\r\n\r\nhello");
};

Worker::runAll();
