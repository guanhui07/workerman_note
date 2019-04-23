<?php

/**
 * event 信号示例
 */

$pid = pcntl_fork();

echo "pid: {$pid}", PHP_EOL; // 可以看到两条输出, 一条是主进程的, 一条是子进程的

if ($pid > 0) {
    sleep(1);

    posix_kill($pid, SIGINT);

    exit(0);
} elseif ($pid == 0) {
    // TODO: 看不懂
    $event_base = new EventBase();

    $event = Event::signal($event_base, SIGINT, function () use (&$event, &$event_base) {
       echo "sigint", PHP_EOL;

       $event->del();

       $event_base->free();

       exit(0);
    });

    $event->add();

    $event_base->loop();
} else {
    printf("fork failed");
}