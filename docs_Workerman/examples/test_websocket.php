<?php

use Workerman\Worker;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * http://doc.workerman.net/315113
 * 
 * 实例二、使用WebSocket协议对外提供服务
 *
 * 运行命令 php web_socket_test.php start 
 *
 **/

 /**
  * 服务端
  */
// 注意：这里与上个例子不通，使用的是websocket协议
$ws_worker = new Worker("websocket://0.0.0.0:2000");

// 启动4个进程对外提供服务
$ws_worker->count = 4;

// 当收到客户端发来的数据后返回hello $data给客户端
$ws_worker->onMessage = function($connection, $data) {
    var_dump("收到数据: " . $data);

    // 向客户端发送hello $data
    $connection->send('hello ' . $data);
};

// 运行worker
Worker::runAll();

/*
* 客户端:

打开chrome浏览器，按F12打开调试控制台，在Console一栏输入(或者把下面代码放入到html页面用js运行)

// 假设服务端ip为127.0.0.1
ws = new WebSocket("ws://127.0.0.1:2000");

ws.onopen = function() {
    alert("连接成功");
    ws.send('tom');
    alert("给服务端发送一个字符串：tom");
};

ws.onmessage = function(e) {
    alert("收到服务端的消息：" + e.data);
};
*/
