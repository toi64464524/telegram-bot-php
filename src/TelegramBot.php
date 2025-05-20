<?php

namespace Telegram\Bot;

use Telegram\Bot\Api;
use Telegram\Bot\Types\Update;
use Telegram\Bot\Types\Filters;
use Telegram\Bot\Types\InputMessage;
use Telegram\Bot\Markups\InlineCallbackKeyboardMarkup;
use Telegram\Bot\Markups\KeyboardMarkup;
use Telegram\Bot\Handlers\StateHandler;
use Telegram\Bot\Handlers\InlineCallbackHandler;
use Telegram\Bot\Handlers\InlineCallbackHandlers;
use Telegram\Bot\Handlers\CommandHandler;
use Telegram\Bot\Handlers\CommandHandlers;
use Telegram\Bot\Handlers\MiddlewareHandler;
use Telegram\Bot\Handlers\MiddlewareHandlers;
use Telegram\Bot\Handlers\Handlers;
use Telegram\Bot\Handlers\MessageHandler;
use Telegram\Bot\Handlers\MessageHandlers;
use Telegram\Bot\Handlers\KeyboardHandler;
use Telegram\Bot\Handlers\KeyboardHandlers;

class TelegramBot extends Api
{
    public ?KeyboardMarkup $keyboard_markup;
    public ?StateHandler $state_handler;
    public array $middleware_handlers;
    public array $command_handlers;
    public array $handlers;
    public array $chat_data;
    public array $user_data;
    private int $update_id;
    public int $id;
    public string $token;

    public function __construct(string $token)
    {
        parent::__construct($token);
        $this->state_handler=null;
        $this->handlers = [];
        $this->middleware_handlers = [];
        $this->command_handlers  = [];
        $this->id = (int) explode(":", $token)[0];
        $this->token = $token;
        $this->update_id = 1;
    }

    public function __init() 
    {
        // 让子类定义
    }

    public function send(int $chat_id, InputMessage $message)
    {
        $params = $message->make($chat_id);
        return parent::sendMessage($params);
    }

    public function edit_message(int $chat_id, int $message_id, array $params)
    {
        $params['chat_id'] = $chat_id;
        $params['message_id'] = $message_id;

        parent::editMessage(($params));
    }

    public function delete_message(int $chat_id, int $message_id)
    {
        $params = [
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ];
        parent::deleteMessage($params);
    }

    public function send_message(string|int $chat_id, array $params, InlineCallbackKeyboardMarkup|KeyboardMarkup $markup=null)
    {
        $params['chat_id'] = $chat_id;
        $params['parse_mode'] = isset($params['parse_mode']) ? $params['parse_mode'] : 'HTML';
        if ($markup){
            $params['reply_markup'] = $markup->make();
        }

        return parent::sendMessage($params);
    }

    public function send_message_text(string|int $chat_id, string $text, array $params=[], InlineCallbackKeyboardMarkup|KeyboardMarkup $markup=null)
    {
        $params['chat_id'] = $chat_id;
        $params['text'] = $text;
        $params['parse_mode'] = isset($params['parse_mode']) ? $params['parse_mode'] : 'HTML';
        if ($markup){
            $params['reply_markup'] = $markup->make();
        }
        return parent::sendMessage($params);
    }

    public function add_handler(MiddlewareHandler|StateHandler|InlineCallbackHandler|MessageHandler|KeyboardHandler|CommandHandler $handler)
    {
        if ($handler instanceof CommandHandler) {
            array_push($this->command_handlers, $handler);

        } else if ($handler instanceof KeyboardHandler) {
            array_push($this->command_handlers, $handler);

        } else if ($handler instanceof InlineCallbackHandler) {
            array_push($this->handlers, $handler);

        } else if ($handler instanceof MessageHandler) {
            array_push($this->handlers, $handler);

        } else if ($handler instanceof MiddlewareHandler) {
            array_push($this->middleware_handlers, $handler);

        } else if ($handler instanceof StateHandler) {
            $this->state_handler = $handler;
        }

        usort($this->middleware_handlers, function($a, $b) {
            return $a->group <=> $b->group;
        });

        usort($this->handlers, function($a, $b) {
            return $a->group <=> $b->group; 
        });
    }

    public function add_handlers(Handlers $handlers)
    {
        foreach ($handlers->handlers as $handler) {
            $this->add_handler($handler);
        }
    }
    
    public function getUpdates(array $options = [], bool $shouldDispatchEvents = true): array
    {
        $response = $this->get('getUpdates', $options);
        return collect($response->getResult())
            ->map(function ($data) use ($shouldDispatchEvents): Update {
                $update = new Update($data);
                $update->setBot($this);
                if ($shouldDispatchEvents) {
                    $this->dispatchUpdateEvent($update);
                }
                return $update;
            })
            ->all();
    }

    // 循环处理消息
    public function run(array $options=[])
    {
        empty($options['limit']) && $options['limit'] = 100;
        empty($options['offset']) && $options['offset'] = 1;
        while(True) {
            try {
                $options['offset'] = $this->update_id + 1;
                foreach ($this->getUpdates($options) as $update) {
                    $this->update_id = $update->update_id;
                    $this->exec_handlers($update);
                }
            } catch (\Exception $e) {
                // Log::error($th);
                var_dump($e->getMessage());
            }
            
        }
    }

    // 没有返回值或者返回值是 false 就停止后续控制器业务
    public function exec_handlers(Update $update)
    {
        $state_id = null;
        if ($this->state_handler) {
            $state_type = $this->state_handler->state_type;
            $state_id = $state_type === StateHandler::CHAT_STATE ? $update->getChat()->id : (StateHandler::USER_STATE ?  $update->getFrom()->id : null);
        }
        
        // 中间件控制器
        var_dump("匹配中间件控制器");
        $state = $this->_handler_handlers($update, $this->middleware_handlers, $state_id);
        if ($state) {
            return;
        }

        // 命令控制器
        var_dump("匹配命令控制器");
        $state = $this->_handler_handlers($update, $this->command_handlers, $state_id);
        if ($state) {
            return;
        }

        // 状态控制器
        if ($state_id) {
            var_dump("匹配状态控制器");
            $state_data = $this->state_handler->get_state_date($state_id);
            // 如果没有开启状态先匹配下 是否要开启状态
            if (!$state_data) {
                $state = $this->_handler_handlers($update, $this->state_handler->entry_point_handlers, $state_id);
                if ($state) {
                    return $this->state_handler->save_state_data($state_id, $state);
                }
            } else if ($state_data) { // 如果已经开启了状态了
                // 先匹配是否关闭的处理器
                $state = $this->_handler_handlers($update, $this->state_handler->fallback_handlers, $state_id);
                if ($state) {
                    return $this->state_handler->save_state_data($state_id, null);
                }

                $state_handlers = $this->state_handler->state_handlers[$state_data];
                // 在匹配状态的处理方法
                $state = $this->_handler_handlers($update, $state_handlers, $state_id);
                if ($state) {
                    return $this->state_handler->save_state_data($state_id, $state);
                }
                // 停止普通方法的匹配
                return null;
            }
        }

        var_dump("匹配普通控制器");
        // 最后在匹配普通的状态
        $state = $this->_handler_handlers($update, $this->handlers, $state_id);
        if ($state) {
            return $this->state_handler->save_state_data($state_id, $state);
        }
    }

    // 循环匹配处理器
    private function _handler_handlers(Update $update, array $handlers, int $state_id=null)
    {
        foreach ($handlers as $handler) {
            $filters = $handler->filters;
            $method = $handler->handler;
            if($filters->handler($update)) {
                var_dump("循环控制器");
                // 匹配成功返回执行结果  后续根据返回结果决定是否继续执行其他控制器
                $state = call_user_func($method, $this, $update);
                // 如果控制器有返回值 true 就停止
                if ($state) {
                    if ($state_id && $state === StateHandler::END) {
                        $this->state_handler->save_state_data($state_id, null);
                    } 
                    return $state;
                }
            }
        }

        // 未匹配 继续其他控制器
        return null;
    }
}