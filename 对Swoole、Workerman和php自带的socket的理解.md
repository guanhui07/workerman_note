## 对Swoole、Workerman和php自带的socket的理解
* http://www.3mu.me/%E5%AF%B9swoole%E3%80%81workerman%E5%92%8Cphp%E8%87%AA%E5%B8%A6%E7%9A%84socket%E7%9A%84%E7%90%86%E8%A7%A3/

为什么php自带的socket不怎么听说，基本都是用swoole,workerman去实现？

1、PHP 的 socket 扩展是一套 socket api，仅此而已。
swoole，用C实现，它的socket是C 库的socket，更加底层可控。
workerman，如题主所说，使用PHP实现，那它的socket就是用PHP socket扩展啊。只是对其进行工程化开发，成了一个框架。

2、swoole 框架和 swoole 扩展是不一样的。
swoole 框架也可以脱离 swoole 扩展来使用。
swoole 扩展将进程管理，tcp 监听这些工作在 C 里面做了，以扩展的形式提供给 PHP 一些接口来调用。
workerman 就是原生的使用 php stream 相关的函数来监听 tcp，进行进程管理。
如果你想学习 PHP 开发 tcp 的原理建议看 workerman 的源码，如果你只是想使用，用 swoole 就 OK 了。

3、就是一个底层通信框架，基于socket通信的，其实PHP 也能做多进程编程（扩展支持，workerman是这方面NO.1），swoole使用C 实现的一套PHP扩展，基于它也是做这方面的功能，因为这两个底层框架的存在，大大扩展了PHP的应用范围，和不错的未来期望

4、php现有的应用方式都是基于http的，对于需要快速实时响应的情况比较乏力，比如网络游戏或者推送服务一般都需要与用户长期保持一个tcp连接以便实时响应和推送信息。
swoole就是解决这样应用场景的。
这个是让php自身建立一个服务，不需要nginx之类的代理，直接监听端口实现通信。
一种应用方式也可以代替掉nginx做http服务，但一般不会这么用。

5、实际上作为一名PHP程序员，我很清楚PHP的确有很多局限性，比如Unix系统编程、网络通信编程、异步io，大部分PHPer不懂。PHP界也确实没有这样的东西。Swoole开源项目就是为了弥补PHP在这些方面的缺陷诞生的。与WordPress这些产品不同，swoole实际上是一个网络通信和异步io的引擎，一个基础库。PHPer可以基于swoole去实现过去PHP无法实现的功能。swoole为PHPer打开了通往另一个世界的大门。

6、这2个框架都很出名，它们的出现大大的提高了php的应用范围及知名度

workerman和swoole都是php socket 服务器框架，都支持长连接、tcp和udp、websocket、异步、分布式部署等

workerman纯php写的，swoole是php的c扩展，性能肯定更高，百度、腾讯不少产品的server就是基于swoole的

workerman上手更快，文档更丰富，社区活跃，社区基本做到有问必答，一般的中小型项目也够了，所以初学者最好还是使用workerman，熟了后再根据具体业务权衡（官方网站都有压测数据）

扩展阅读：
Yii/Yaf/Swoole3个框架的压测性能对比：http://rango.swoole.com/archives/254
swoole与phpdaemon/reactphp/workerman等纯PHP网络库的差异：http://rango.swoole.com/archives/334
国内C源码PHP框架选择与评价 Yaf Yar Swoole workerman？：http://www.zhihu.com/question/24493908