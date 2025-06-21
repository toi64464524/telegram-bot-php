<?php

namespace Telegram\Bot;

use Telegram\Bot\Handlers\Handler;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\UserData;
use Telegram\Bot\Objects\Message;

/**
 * TelegramBotHandlers TelegramBotHandlers
 */
class TelegramBotHandlers
{
    const USER_STATE = 'user';
    const CHAT_STATE = 'chat';
    const END = -1;

    // 状态类型
    private string $state_type;

    // 中间件控制器 最先执行
    private array $middleware_handlers=[];
    
    // 命令控制器
    private array $command_handlers=[];

    // 状态控制器 二维数组
    private array $state_handler= [];

    // 普通处理器
    private array $handlers=[];

    // 状态存储
    private array $state_data =[];

    // 窗口的上一次消息
    private array $chat_last_message=[];

    // 用户数据缓存 内存存储 <UserData>
    private array $user_data=[];

    public function __construct(string $state_type = null)
    {
        $this->state_type = $state_type;
    }

    // 获取状态类型
    public function getHandlerStateType()
    {
        return $this->state_type;
    }

    // 添加控制器 普通的 第二个参数 传入为通用
    public function addHandler(Handler $handler, string $state=null)
    {
        array_push($this->handlers, $handler);
        usort($this->handlers, function($a, $b) {
            return $a->getGroup() <=> $b->getGroup(); 
        });

        if ($state) {
            $this->addStatehandler($state, $handler);
        }
    }

    // 添加状态控制器
    public function addStateHandler(string $state, Handler $handler)
    {
        if (!isset($this->state_handler[$state])) {
            $this->state_handler[$state] = [];
        }
        array_push($this->state_handler[$state], $handler);
        usort($this->state_handler[$state], function($a, $b) {
            return $a->getGroup() <=> $b->getGroup(); 
        });
    }

    // 添加中间件控制器
    public function addMiddlewareHandler(Handler $handler)
    {
        array_push($this->middleware_handlers, $handler);
        usort($this->middleware_handlers, function($a, $b) {
            return $a->getGroup() <=> $b->getGroup(); 
        });
    }

    // 添加命令控制器
    public function addCommandHandler(Handler $handler)
    {
        array_push($this->command_handlers, $handler);
        usort($this->command_handlers, function($a, $b) {
            return $a->getGroup() <=> $b->getGroup(); 
        });
    }

    // 匹配控制器 依次执行
    public function match(Api $telegram, Update $update)
    {
        $update->setTelegram($telegram);
        if ($this->matchMiddlewarehandlers($telegram, $update) !== null) {
            return;
        }

        echo "匹配命令控制器\n";
        if ($this->matchCommandhandlers($telegram, $update) !== null) {
            return;
        }
        echo "匹配状态控制器\n";
        if ($this->state_type && $this->matchStatehandlers($telegram, $update) !== null) {
            return;
        }
        echo "匹配一般控制器\n";
        if ($this->matchhandlers($telegram, $update) !== null) {
            return;
        } 
    }

    // 匹配中间件控制器
    private function matchMiddlewarehandlers(Api $telegram, Update $update) 
    {
        return $this->execHandlers($telegram, $update, $this->middleware_handlers);
    }

    // 匹配状态控制器
    private function matchStatehandlers(Api $telegram, Update $update)
    {
        $state_id = $this->getStateId($telegram, $update);
        if (!$state_id) {
            return null;
        }  

        if (!isset($this->state_handler[$this->getState($state_id)])) {
            return null;
        }

        $handlers = $this->state_handler[$this->getState($state_id)];
        return $this->execHandlers($telegram, $update, $handlers);
    }

    // 匹配命令控制器
    private function matchCommandhandlers(Api $telegram, Update $update)
    {
        return $this->execHandlers($telegram, $update, $this->command_handlers);
    }

    // 匹配普通控制器
    private function matchhandlers(Api $telegram, Update $update)
    {
        return $this->execHandlers($telegram, $update, $this->handlers);
    }

    // 循环控制器
    private function execHandlers(Api $telegram, Update $update, array $handlers)
    {   
        $state_id = $this->getStateId($telegram, $update);
        foreach ($handlers as $handler) {
            $filters = $handler->getFilters();
            $method = $handler->getHandler();
            // 如果没有设置过滤器，直接执行
            if($filters->handler($update)) {
                $state = call_user_func($method, $telegram, $update);
                if ($state_id && $state instanceof \Telegram\Bot\Objects\Message) {
                    $this->chat_last_message[$state_id] = $state;
                }
                // 如果返回值是字符串，表示状态
                if ($state_id && is_string($state)) {
                    $this->setState($state_id, $state);
                }
                if ($state_id && $state === self::END) {
                    $this->setState($state_id, null);
                }

                // 如果有返回值结束控制器
                if ($state!==null) {
                    return $state;
                }
            }
        }

        // 未匹配 继续其他控制器
        return null;
    }

    // 获取用户的id
    public function getStateId(Api $telegram, Update $update) :?int
    {
        if ($this->state_type === self::CHAT_STATE) {
           return $update->getChatId();
        } else if ($this->state_type === self::USER_STATE) {
            return $update->getFromId();
        } else {
            return null;
        }
    }

    // 获取当前状态
    public function getState(int $id) : ?string
    {
        if (isset($this->state_data[$id])) {
            return $this->state_data[$id];
        }
        return null;
    }

    // 设置当前状态
    public function setState(int $id, ?string $state)
    {
        $this->state_data[$id] = $state;
    }

    // 设置缓存数据
    public function setUserData(int $id, string $key, $value): bool 
    {
        if (!isset($this->user_data[$id])) {
            $this->user_data[$id] = new UserData;
        }
        $user_data = $this->user_data[$id];
        return $user_data->set($key, $value);
    }

    // 获取缓存数据
    public function getUserData(int $id, string $key)
    {
        if (!isset($this->user_data[$id])) {
            $this->user_data[$id] = new UserData;
        }
        $user_data = $this->user_data[$id];
        return $user_data->get($key);
    }

    // 获取上一次发送的消息
    public function getLastMessage(Api $telegram, Update $update) :?Message
    {
        $state_id = $this->getStateId($telegram, $update);
        if ($state_id && isset($this->chat_last_message[$state_id])) {
            $message = $this->chat_last_message[$state_id];
            $message->setTelegram($telegram);
        }

        return null;
    }
}