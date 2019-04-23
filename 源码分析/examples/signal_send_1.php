<?php

// declare方式 处理信号
declare(ticks = 1);

$flag = true;

// pcntl_signal — 安装一个信号处理器
// 在终端 ctrl + c 中断脚本的时候会触发这里
pcntl_signal(SIGINT, function ($signal) use (&$flag) {
    echo 'sigint', PHP_EOL;

    $flag = false;
});

// posix_getpid — 返回当前进程 id
file_put_contents('pid_1.txt', posix_getpid());

// some task
while ($flag) {
    echo date('h:i:s') . "\n";

    // usleep — 以指定的微秒数延迟执行
    usleep(1000000);

    echo date('h:i:s') . "\n\n";
}
