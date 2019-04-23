<?php

/**
 * Event 定时器示例
 */

function setTimeout($second, $callback)
{
    $event_base = new EventBase();

    $event = Event::timer($event_base, $callback);

    $event->add($second);

    $event_base->loop();
}

function setInterval($second, $callback)
{
    $func = function() use ($second, $callback, &$func) {
        call_user_func($callback);

        // 注释掉这句, 就只会在一秒后运行一次
        setTimeout($second, $func);
    };

    setTimeout($second, $func);
}

setInterval(1, function() {
    echo "hello" . PHP_EOL;
});