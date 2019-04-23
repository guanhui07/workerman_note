<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Workerman\Lib;

use Workerman\Events\EventInterface;
use Exception;

/**
 * Timer.
 *
 * TODO: 定时器类， 这是一个静态类， 可以单独抽取出来
 *
 * example:
 * Workerman\Lib\Timer::add($time_interval, callback, array($arg1, $arg2..));
 */
class Timer
{
    /**
     * Tasks that based on ALARM signal.
     * [
     *   run_time => [[$func, $args, $persistent, time_interval],[$func, $args, $persistent, time_interval],..]],
     *   run_time => [[$func, $args, $persistent, time_interval],[$func, $args, $persistent, time_interval],..]],
     *   ..
     * ]
     *
     * @var array
     */
    protected static $_tasks = array();

    /**
     * event
     *
     * @var \Workerman\Events\EventInterface
     */
    protected static $_event = null;

    /**
     * Init.
     *
     * @param \Workerman\Events\EventInterface $event
     * @return void
     */
    public static function init($event = null)
    {
        if ($event) {
            self::$_event = $event;
        } else {
            // 安装一个信号处理器: 函数pcntl_signal()为 signo 指定的信号安装一个新 的信号处理器。
            // http://php.net/manual/zh/function.pcntl-signal.php
            pcntl_signal(SIGALRM, array('\Workerman\Lib\Timer', 'signalHandle'), false);
        }
    }

    /**
     * ALARM signal handler.
     *
     * @return void
     */
    public static function signalHandle()
    {
        if (! self::$_event) {
            // 为进程设置一个alarm闹钟信号: 创建一个计时器，在指定的秒数后向进程发送一个SIGALRM信号。每次对 pcntl_alarm()的调用都会取消之前设置的alarm信号。
            // http://php.net/manual/zh/function.pcntl-alarm.php
            pcntl_alarm(1);

            self::tick();
        }
    }

    /**
     * Add a timer.
     *
     * @param int      $time_interval
     * @param callable $func
     * @param mixed    $args
     * @param bool     $persistent
     * @return int/false
     */
    public static function add($time_interval, $func, $args = array(), $persistent = true)
    {
        if ($time_interval <= 0) {
            echo new Exception("bad time_interval");

            return false;
        }

        if (self::$_event) {
            return self::$_event->add($time_interval,
                $persistent ? EventInterface::EV_TIMER : EventInterface::EV_TIMER_ONCE, $func, $args);
        }

        if (! is_callable($func)) {
            echo new Exception("not callable");

            return false;
        }

        if (empty(self::$_tasks)) {
            pcntl_alarm(1);
        }

        $time_now = time();

        $run_time = $time_now + $time_interval;

        if (! isset(self::$_tasks[$run_time])) {
            self::$_tasks[$run_time] = array();
        }

        self::$_tasks[$run_time][] = array($func, (array)$args, $persistent, $time_interval);

        return 1;
    }


    /**
     * Tick.
     *
     * 定时任务触发
     *
     * @return void
     */
    public static function tick()
    {
        if (empty(self::$_tasks)) {
            pcntl_alarm(0);

            return;
        }

        $time_now = time();

        // 遍历所有任务
        foreach (self::$_tasks as $run_time => $task_data) {
            // 任务时间 小于 当前时间就触发。 TODO： 任务执行太久会不会有影响下个任务的触发？
            if ($time_now >= $run_time) {

                foreach ($task_data as $index => $one_task) {
                    $task_func     = $one_task[0];
                    $task_args     = $one_task[1];
                    $persistent    = $one_task[2];
                    $time_interval = $one_task[3];

                    try {
                        call_user_func_array($task_func, $task_args);
                    } catch (\Exception $e) {
                        echo $e;
                    }

                    if ($persistent) {
                        self::add($time_interval, $task_func, $task_args);
                    }
                }

                unset(self::$_tasks[$run_time]);
            }
        }
    }

    /**
     * Remove a timer.
     *
     * @param mixed $timer_id
     * @return bool
     */
    public static function del($timer_id)
    {
        if (self::$_event) {
            return self::$_event->del($timer_id, EventInterface::EV_TIMER);
        }

        return false;
    }

    /**
     * Remove all timers.
     *
     * @return void
     */
    public static function delAll()
    {
        self::$_tasks = array();

        // 为进程设置一个alarm闹钟信号. 如果seconds设置为0,将不会创建alarm信号。
        pcntl_alarm(0);

        if (self::$_event) {
            self::$_event->clearAllTimer();
        }
    }
}
