<?php

$flag = true;

// 在终端 ctrl + c 中断脚本的时候会触发这里
pcntl_signal(SIGINT, function () use (&$flag) {
//    usleep(1000000);

    echo 'sigint', PHP_EOL;

    $flag = false;
});

file_put_contents("pid_2.txt", posix_getpid());

// some task
while ($flag) {
    usleep(1000000);

    // pcntl_signal_dispatch — 调用等待信号的处理器
    pcntl_signal_dispatch();

    echo 'pcntl_signal_dispatch', PHP_EOL;
}