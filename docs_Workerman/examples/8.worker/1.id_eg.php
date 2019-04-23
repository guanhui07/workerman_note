<?php
// http://doc.workerman.net/315130
/**
进程重启后id编号值是不变的。

进程编号id的分配是基于每个worker实例的。每个worker实例都从0开始给自己的进程编号，所以worker实例间进程编号会有重复，但是一个worker实例中的进程编号不会重复。例如下面的例子：
 */
use Workerman\Worker;

require_once '../../../vendor/autoload.php';

# worker实例 1
// worker实例1有4个进程，进程id编号将分别为0、1、2、3
$worker1 = new Worker('tcp://0.0.0.0:8585');

// 设置启动4个进程 (TODO: 这个数量到底是子进程数量还是主进程数量, 一直没搞懂!!!)
$worker1->count = 4;

// 每个进程启动后打印当前进程id编号即 $worker1->id
$worker1->onWorkerStart = function($worker1) {
    echo "worker1->id={$worker1->id}\n";
};

# worker实例 2
// worker实例2有两个进程，进程id编号将分别为0、1
$worker2 = new Worker('tcp://0.0.0.0:8686');

// 设置启动2个进程
$worker2->count = 2;

// 每个进程启动后打印当前进程id编号即 $worker2->id
$worker2->onWorkerStart = function($worker2) {
    echo "worker2->id={$worker2->id}\n";
};

// 运行worker
Worker::runAll();

/**
输出类似:
worker1->id=0
worker1->id=1
worker1->id=2
worker1->id=3
worker2->id=0
worker2->id=1
*/