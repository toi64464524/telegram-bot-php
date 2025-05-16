<?php

namespace telegram\bot;

use Swoole\Process;
use Swoole\Timer;
use Swoole\Event;

class TelegramBotManager
{
    protected array $bots = [];        // [id => TelegramBot 对象]
    protected array $processes = [];   // [id => process pid]
    protected array $workers = [];     // [id => Process 对象]

    public function _init() {}

    // public function add(int $bot_id, string $bot_token): void
    // {
    //     // $id = (int) explode(":", $bot_token)[0];
    //     $id= $bot_id;
    //     $this->tokens[$id] = $bot_token;
    // }

    public function add(TelegramBot $telegram_bot): void
    {
        // $id = (int) explode(":", $bot_token)[0];
        $id = $telegram_bot->id;
        $this->bots[$id] = $telegram_bot;
    }

    public function start(string $bot_id): void
    {
        if (!isset($this->bots[$bot_id])) {
            echo "Bot {$bot_id} not found\n";
            return;
        }

        if (isset($this->processes[$bot_id])) {
            echo "Bot {$bot_id} is already running\n";
            return;
        }

        // $bot = $this->tokens[$bot_id];
        $telegram_bot = $this->bots[$bot_id];
        $process = new Process(function (Process $worker) use ($telegram_bot) {
            echo "Bot {$telegram_bot->id} process started (PID: ".posix_getpid().")\n";

            // $bot = new TelegramBot($token);
            $telegram_bot->run(); // 持续运行 bot

        }, false, 2, true);

        $pid = $process->start();
        $this->processes[$bot_id] = $pid;
        $this->workers[$bot_id] = $process;
    }

    public function stop(string $bot_id): void
    {
        if (isset($this->processes[$bot_id])) {
            Process::kill($this->processes[$bot_id]);
            Process::wait(true); // 回收子进程资源
            unset($this->processes[$bot_id], $this->workers[$bot_id]);
            echo "Bot {$bot_id} stopped\n";
        } else {
            echo "Bot {$bot_id} is not running\n";
        }
    }

    public function restart(string $bot_id): void
    {
        $this->stop($bot_id);
        $this->start($bot_id);
    }

    public function monitor(int $interval = 5000): void
    {
        Timer::tick($interval, function () {
            foreach ($this->processes as $id => $pid) {
                if (!Process::kill($pid, 0)) {
                    echo "Bot {$id} has exited unexpectedly. Restarting...\n";
                    $this->restart($id);
                }
            }
        });
    }

    public function listBots(): void
    {
        echo "\n--- Bot List ---\n";
        foreach (array_keys($this->bots) as $id) {
            $status = isset($this->processes[$id]) ? 'Running' : 'Stopped';
            echo "[{$id}] {$status}\n";
        }
        echo "----------------\n";
    }

    public function run(): void
    {
        foreach (array_keys($this->bots) as $id) {
            $this->start($id);
        }
        $this->monitor();
        Event::wait(); // 保持主进程运行
    }
}
