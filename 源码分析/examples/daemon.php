<?php

function deamon()
{
    umask(0);

    $pid = pcntl_fork();

    echo "pcntl_fork 第一次 pid: {$pid}", PHP_EOL;

    if ($pid > 0) {
        // 为什么主进程退出: 这是因为调用 posix_setsid 的进程必须不能是 session leader
        exit(0);
    } elseif ($pid < 0) {
        echo "fork failed", PHP_EOL;
    }

    // posix_setsid -- 使当前进程成为会话的领导者
    posix_setsid();

    // 在调用 posix_setsid 之后为什么还要再 fork 一次呢?
    // 其实这必不是必须的, nginx 在实现 daemon 时就没有 fork 两次. 很多 daemon 的实现都没有 fork 两次. 只是有人推荐在 sysv system 上, 再 fork 一次, 可以避免守护进程打开控制终端, 因为再 fork 一次之后, 子进程就不是 session leader 了.
    $pid = pcntl_fork();

    echo "pcntl_fork 第二次 pid: {$pid}", PHP_EOL;

    if ($pid > 0) {
        exit(0);
    } elseif ($pid < 0) {
        echo "fork failed", PHP_EOL;
    }
}

deamon();