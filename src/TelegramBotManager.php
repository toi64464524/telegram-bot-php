<?php

namespace telegram\bot;

use Swoole\Process;
use Swoole\Timer;
use Swoole\Event;

/**
 * TelegramBotManager 类：管理多个 Telegram Bot 进程的启动、停止和监控
 */
class TelegramBotManager
{
    protected array $bots = [];        // 存储所有 Bot 对象 [id => TelegramBot 实例]
    protected array $processes = [];   // 存储运行中的进程 PID [id => pid]
    protected array $workers = [];     // 存储 Process 对象 [id => Process 实例]

    /**
     * 初始化方法（预留，暂无逻辑）
     */
    public function _init() {}

    /**
     * 添加一个 Bot 到管理器
     * @param TelegramBot $telegram_bot Bot 实例
     * @return int 返回 Bot 的 ID
     */
    public function add(TelegramBot $telegram_bot): int
    {
        $id = $telegram_bot->id;
        $this->bots[$id] = $telegram_bot;
        return $id;
    }

    /**
     * 启动指定 Bot 的进程
     * @param string $bot_id Bot ID
     * @return bool 是否启动成功
     */
    public function start(string $bot_id): bool
    {
        // 检查 Bot 是否存在
        if (!isset($this->bots[$bot_id])) {
            echo "Bot {$bot_id} not found\n";
            return false;
        }

        // 检查是否已运行
        if (isset($this->processes[$bot_id])) {
            echo "Bot {$bot_id} is already running\n";
            return false;
        }

        // 创建子进程运行 Bot
        $telegram_bot = $this->bots[$bot_id];
        $process = new Process(function (Process $worker) use ($telegram_bot) {
            echo "Bot {$telegram_bot->id} process started (PID: ".posix_getpid().")\n";
            $telegram_bot->run(); // 启动 Bot 主逻辑
        }, false, 2, true); // 参数：不启用协程，管道类型，重定向标准输入输出

        // 记录进程信息
        $pid = $process->start();
        $this->processes[$bot_id] = $pid;
        $this->workers[$bot_id] = $process;
        return true;
    }

    /**
     * 停止指定 Bot 的进程
     * @param string $bot_id Bot ID
     * @return bool 是否停止成功
     */
    public function stop(string $bot_id): bool
    {
        if (isset($this->processes[$bot_id])) {
            Process::kill($this->processes[$bot_id]); // 发送终止信号
            Process::wait(true); // 回收子进程资源
            unset($this->processes[$bot_id], $this->workers[$bot_id]);
            echo "Bot {$bot_id} stopped\n";
        } else {
            echo "Bot {$bot_id} is not running\n";
        }
        return true;
    }

    /**
     * 重启指定 Bot
     * @param string $bot_id Bot ID
     * @return bool 是否重启成功
     */
    public function restart(string $bot_id): bool
    {
        if ($this->stop($bot_id)) {
            return $this->start($bot_id);
        }
        return false;
    }

    /**
     * 监控所有 Bot 进程状态（自动重启崩溃的进程）
     * @param int $interval 监控间隔（毫秒）
     */
    private function _monitor(int $interval = 5000): void
    {
        Timer::tick($interval, function () {
            foreach ($this->processes as $id => $pid) {
                // 检查进程是否存活（kill(0) 不发送信号，仅检测）
                if (!Process::kill($pid, 0)) {
                    echo "Bot {$id} has exited unexpectedly. Restarting...\n";
                    $this->restart($id); // 自动重启
                }
            }
        });
    }

    /**
     * 启动所有 Bot 并开始监控
     */
    public function run(): void
    {
        // 启动所有已注册的 Bot
        foreach (array_keys($this->bots) as $id) {
            $this->start($id);
        }

        // 开启监控
        $this->_monitor();

        // 保持主进程运行（事件循环）
        Event::wait();
    }
}