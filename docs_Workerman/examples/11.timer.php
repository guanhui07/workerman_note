<?php

use Workerman\Worker;
use \Workerman\Lib\Timer;

require_once '../../vendor/autoload.php';

$task = new Worker();

// 开启多少个进程运行定时任务，注意业务是否在多进程有并发问题
$task->count = 1;

$task->onWorkerStart = function($task) {
    // 每2.5秒执行一次
    $time_interval = 2;

    Timer::add($time_interval, function() {
        echo "task run\n";
    });
};

// 运行worker
Worker::runAll();