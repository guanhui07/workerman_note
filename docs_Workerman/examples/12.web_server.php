<?php

use Workerman\Worker;
use Workerman\WebServer;

require_once '../../vendor/autoload.php';

// 这里监听8080端口，如果要监听80端口，需要root权限，并且端口没有被其它程序占用
$webserver = new WebServer('http://0.0.0.0:8080');

// TODO: addRoot 到底怎么用?
// 类似 nginx 配置中的 root 选项，添加域名与网站根目录的关联，可设置多个域名多个目录
$webserver->addRoot('www.example.dev', '/mnt/e/codes/workerman/');

// $webserver->addRoot('blog.example.dev', '/mnt/e/codes/workerman/images/');

// 设置开启多少进程
$webserver->count = 4;

Worker::runAll();
