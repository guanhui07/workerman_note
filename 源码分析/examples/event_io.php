<?php

/**
 *
 * Event I/O 示例
 *
 * 需要借助文档理解, EventBase 和 Event 这两个类的使用.
 *
 * TODO:
 *  1. 没理解这段代码怎么使用? 如何访问
 *  2. 如果没有 Event 有什么区别?
 */

$fd = stream_socket_server("tcp://0.0.0.1:9001", $errno, $errstr);

stream_set_blocking($fd, 0);

$event_base = new EventBase();

$event = new Event($event_base, $fd, Event::READ | Event::PERSIST, function ($fd) use (&$event_base) {
    $conn = stream_socket_accept($fd);

    fwrite($conn, "HTTP/1.0 200 OK\r\nContent-Length: 2\r\n\r\nHi");

    fclose($conn);
}, $fd);

$event->add();

$event_base->loop();