<?php

$fd = stream_socket_server("tcp://0.0.0.0:8090", $errno, $errstr);

stream_set_blocking($fd, 0);

// stream_context_set_option($fd, 'socket', 'so_reuseport', 1);

$event_base = new EventBase();

$event = new Event($event_base, $fd, Event::READ | Event::PERSIST, function ($fd) use (&$event_base) {
    $startDate = date('Y-m-d H:i:s');

    $conn = stream_socket_accept($fd);

    sleep(5); // 假设有业务阻塞在这里

    $endDate = date('Y-m-d H:i:s');

    $message = "Hi {$startDate} {$endDate}";

    $len = strlen($message);

    fwrite($conn, "HTTP/1.0 200 OK\r\nContent-Length: $len\r\n\r\n$message");

    fclose($conn);
}, $fd);

$event->add();

$event_base->loop();