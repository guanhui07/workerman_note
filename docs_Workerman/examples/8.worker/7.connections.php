<?php

use Workerman\Worker;
use Workerman\Lib\Timer;

require_once '../../../vendor/autoload.php';

$worker = new Worker('text://0.0.0.0:2020');

// 设置 count 能更好的理解 connections
$worker->count = 2;

// 进程启动时设置一个定时器，定时向所有客户端连接发送数据
$worker->onWorkerStart = function($worker) {
    // 定时，每10秒一次
    Timer::add(3, function()use($worker) {
        $count = count($worker->connections);
        echo "connections count: {$count}\n";

        // 遍历当前进程所有的客户端连接，发送当前服务器的时间
        foreach($worker->connections as $connection) {
            $connection->send(time());
        }
    });
};

// 运行worker
Worker::runAll();