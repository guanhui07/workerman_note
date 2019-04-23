<?php

$fd = stream_socket_server("tcp://0.0.0.0:8090", $errno, $errstr);

stream_set_blocking($fd, 0);

while (true) {


    $conn = stream_socket_accept($fd);

    $pid = pcntl_fork();

    if ($pid == 0) {
        $startDate = date('Y-m-d H:i:s');

        sleep(5); // 假设有业务阻塞在这里

        $endDate = date('Y-m-d H:i:s');

        $message = "Hi {$startDate} {$endDate}";

        $len = strlen($message);

        // socket 也是文件, 所以可以使用诸如 read, write 这样的函数.
        fwrite($conn, "HTTP/1.0 200 OK\r\nContent-Length: $len\r\n\r\n$message");

        fclose($conn);

        exit(0);
    } elseif ($pid > 0) {
        continue;
    } else {
        printf("fork failed");
    }
}