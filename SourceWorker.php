<?php

use Workerman\Worker;
use Workerman\Lib\Timer;
use Workerman\Events\EventInterface;

require_once __DIR__ . '/vendor/autoload.php';

// $task = new Worker();
// // 定时器在 php 7.0 上有bug : http://wenda.workerman.net/?/question/1179
// $task->onWorkerStart = function ($task) {
//     // 每2秒执行一次
//     Timer::add(2, function () {
//         // $date = Date('Y-m-d H:i:s');
//         echo "task run\n";
//     });
// };

// 此属性为全局静态属性，如果以守护进程方式(-d启动)运行，则所有向终端的输出(echo var_dump等)都会被重定向到stdoutFile指定的文件中
// 如果不设置，并且是以守护进程方式运行，则所有终端输出全部重定向到/dev/null
Worker::$stdoutFile = '/tmp/stdout.log';

// 此属性为全局静态属性，用来设置WorkerMan进程的pid文件路径
// 如果无特殊需要，建议不要设置此属性
// 此项设置在监控中比较有用，例如将WorkerMan的pid文件放入固定的目录中，可以方便一些监控软件读取pid文件，从而监控WorkerMan进程状态
// Worker::$pidFile = '/var/run/workerman.pid';

// 此文件记录了workerman自身相关的日志，包括启动、停止等
// 如果没有设置，文件名默认为workerman.log，文件位置位于Workerman的上一级目录中
Worker::$logFile = '/tmp/workerman.log';

// 此属性为全局静态属性，表示是否以daemon(守护进程)方式运行。如果启动命令使用了 -d参数，则该属性会自动设置为true。
// Worker::$daemonize = true;

// 下面这个针对 0 进程的定时器没有问题. TODO: 上面的 $task 为啥子不行
$worker = new Worker('websocket://0.0.0.0:8585');

// 设置当前Worker实例启动多少个进程，不设置时默认为1
$worker->count = 4;

// 置当前Worker实例的名称，方便运行status命令时识别进程。不设置时默认为none
$worker->name = 'Worker Name: 定时器';

// 设置当前Worker实例的协议类
// $worker->protocol = 'Workerman\\Protocols\\Http';

// 设置当前Worker实例所使用的传输层协议，目前只支持3种(tcp、udp、ssl)。不设置默认为tcp
// $worker->transport = 'udp';

// 设置当前worker是否开启监听端口复用(socket的SO_REUSEPORT选项)，默认为false，不开启

// 开启监听端口复用后允许多个无亲缘关系的进程监听相同的端口，并且由系统内核做负载均衡，决定将socket连接交给哪个进程处理，避免了惊群效应，可以提升多进程短连接应用的性能
// 注意： 此特性需要PHP版本>=7.0
$worker->reusePort = true;

// 设置当前Worker实例以哪个用户运行。此属性只有当前用户为root时才能生效。不设置时默认以当前用户运行
// 建议$user设置权限较低的用户，例如www-data、apache、nobody等
// $worker->user = 'www-data';

// 设置当前Worker实例是否可以reload，即收到reload信号后是否退出重启。不设置默认为true，收到reload信号后自动重启进程
$worker->reloadable = false;

// 设置Worker启动时的回调函数，即当Worker启动后立即执行Worker::onWorkerStart成员指定的回调函数
$worker->onWorkerStart = function ($worker) {
    echo "回调属性 onWorkerStart\n";

    // linux 下运行  `ps -a` 就能看到每个进程
    // 在 kill 某个进程后 workerman 会再次启动它, 并看到如下:
    // worker[Worker Name: 定时器:15556] exit with status 15
    // Pid is 15844
    echo 'Pid is ' . posix_getpid() . "\n";

    // 当进程收到SIGALRM信号时，打印输出一些信息
    Worker::$globalEvent->add(SIGALRM, EventInterface::EV_SIGNAL, function () {
        echo "Get signal SIGALRM\n";
    });

    // 当前worker进程的id编号，范围为0到$worker->count-1。
    // 只在id编号为0的进程上设置定时器，其它1、2、3号进程不设置定时器
    if ($worker->id === 0) {
        Timer::add(5, function () use ($worker) {
            $date = Date('Y-m-d H:i:s');

            echo "4个worker进程，只在0号进程 ({$worker->id}) 设置定时器 {$date}\n";

            // 此属性中存储了当前进程的所有的客户端连接对象，其中id为connection的id编号，详情见手册TcpConnection的id属性。
            // $connections 在广播时或者根据连接id获得连接对象非常有用。
            // 遍历当前进程所有的客户端连接，发送当前服务器的时间
            foreach ($worker->connections as $connection) {
                $t = time();

                echo "connection {$t} \n";

                // 这里需要有客户端链接到服务端, 就能接收到每个 connetion 发出的消息
                $connection->send($t);
            }
        });
    }
};

// 此特性不常用到 http://doc.workerman.net/315146
// 子进程收到reload信号默认的动作是退出重启，以便新进程重新加载业务代码完成代码更新。所以reload后子进程在执行完onWorkerReload回调后便立刻退出是正常现象。
// 如果在收到reload信号后只想让子进程执行onWorkerReload，不想退出，可以在初始化Worker实例时设置对应的Worker实例的reloadable属性为false。
$worker->onWorkerReload = function ($worker) {
    echo "回调属性 onWorkerReload\n";

    foreach ($worker->connections as $connection) {
        $connection->send('worker reloading');
    }
};

// http://doc.workerman.net/315147
// 当客户端与Workerman建立连接时(TCP三次握手完成后)触发的回调函数。每个连接只会触发一次onConnect回调。
// 注意：onConnect事件仅仅代表客户端与Workerman完成了TCP三次握手，这时客户端还没有发来任何数据，此时除了通过$connection->getRemoteIp()获得对方ip，没有其他可以鉴别客户端的数据或者信息，所以在onConnect事件里无法确认对方是谁。要想知道对方是谁，需要客户端发送鉴权数据，例如某个token或者用户名密码之类，在onMessage回调里做鉴权。
$worker->onConnect = function ($connection) {
    echo "回调属性 onConnect\n";

    echo "new connection from ip " . $connection->getRemoteIp() . "\n";
};

$worker->onClose = function ($connection) {
    echo "回调属性 onClose\n";

    echo "connection closed\n";
};

// http://doc.workerman.net/315148
// $connection 连接对象，即TcpConnection实例，用于操作客户端连接，如发送数据，关闭连接等
// $data 客户端连接上发来的数据，如果Worker指定了协议，则$data是对应协议decode（解码）了的数据
$worker->onMessage = function ($connection, $data) {
    var_dump($data);
    $connection->send('receive success');
};

$worker->onError = function ($connection, $code, $msg) {
    echo "error $code $msg\n";
};

// 运行worker
// Worker::runAll()执行后将永久阻塞，也就是说位于Worker::runAll()后面的代码将不会被执行。所有Worker实例化应该都在Worker::runAll()前进行。
Worker::runAll();
