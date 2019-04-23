<?php
/**
 * 设置当前Worker实例是否可以reload，即收到reload信号后是否退出重启。不设置默认为true，收到reload信号后自动重启进程。
 */
use Workerman\Worker;

require_once '../../../vendor/autoload.php';

$worker = new Worker('websocket://0.0.0.0:8484');

// 设置此实例收到reload信号后是否reload重启
$worker->reloadable = true;

$worker->onWorkerStart = function($worker) {
    echo "Worker starting...\n";
};

// 运行worker
Worker::runAll();

/**
 
1. start: php 12.reloadable.php start

2. 然后 reload : php 12.reloadable.php reload
    * 设置 reloadable 为 true 才能再 reload 的时候再次触发 `onWorkerStart` 回调
 */