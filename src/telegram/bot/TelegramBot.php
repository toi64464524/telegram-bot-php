<?php

namespace telegram\bot;

use Telegram\Bot\Api;

use telegram\bot\types\Update;
use telegram\bot\types\Filters;
use telegram\bot\types\InlineCallbackHandler;
use telegram\bot\types\InlineCallbackHandlers;
use telegram\bot\types\MessageHandler;
use telegram\bot\types\MessageHandlers;
use telegram\bot\types\KeyboardHandler;
use telegram\bot\types\KeyboardHandlers;
use telegram\bot\types\CommandHandler;
use telegram\bot\types\CommandHandlers;
use telegram\bot\types\MiddlewareHandler;
use telegram\bot\types\MiddlewareHandlers;
use telegram\bot\types\StateHandler;
use telegram\bot\types\InlineCallbackKeyboardMarkup;
use telegram\bot\types\KeyboardMarkup;
// use Illuminate\Support\Facades\Log;


class TelegramBot extends Api
{
    public ?KeyboardMarkup $keyboard_markup;
    public ?StateHandler $state_handler;
    public array $middleware_handlers;
    public array $handlers;
    public array $chat_data;
    public array $user_data;
    private int $update_id;

    public function __construct(string $token)
    {
        parent::__construct($token);
        $this->state_handler=null;
        // $this->keyboard_markup = null;
        $this->handlers = [];
        $this->middleware_handlers = [];
        // $this->chat_data = [];
        $this->update_id = 1;
    }

    public function __init() {}

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
        // dd($params);
        return parent::sendMessage($params);
    }

    public function add_handler(MiddlewareHandler|StateHandler|InlineCallbackHandler|MessageHandler|KeyboardHandler|CommandHandler $handler)
    {
        if ($handler instanceof CommandHandler) {
            array_push($this->handlers, $handler->handler);
        } else if ($handler instanceof KeyboardHandler) {
            array_push($this->handlers, $handler->handler);
        } else if ($handler instanceof InlineCallbackHandler) {
            array_push($this->handlers, $handler->handler);
        } else if ($handler instanceof MessageHandler) {
            array_push($this->handlers, $handler->handler);
        } else if ($handler instanceof MiddlewareHandler) {
            array_push($this->middleware_handlers, $handler->handler);
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

    public function add_handlers(MiddlewareHandlers|InlineCallbackHandlers|MessageHandlers|KeyboardHandlers|CommandHandlers $handlers)
    {
        if ($handlers instanceof CommandHandlers) {
            $this->handlers = array_merge($this->handlers, $handlers->handlers);
            // $this->setMyCommands($handlers->commands);
        } else if ($handlers instanceof KeyboardHandlers) {
            $this->handlers = array_merge($this->handlers, $handlers->handlers);
        } else if ($handlers instanceof InlineCallbackHandlers) {
            $this->handlers = array_merge($this->handlers, $handlers->handlers);
        } else if ($handlers instanceof MessageHandlers) {
            $this->handlers = array_merge($this->handlers, $handlers->handlers);
        } else if ($handlers instanceof MiddlewareHandlers) {
            $this->handlers = array_merge($this->middleware_handlers, $handlers->handlers);
        }
        usort($this->middleware_handlers, function($a, $b) {
            return $a->group <=> $b->group; 
        });
        usort($this->handlers, function($a, $b) {
            return $a->group <=> $b->group; 
        });
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
            } catch (\Exception $th) {
                // Log::error($th);
            }
            
        }
    }

    public function exec_handlers(Update $update)
    {
        $group = null;
        $id = null;
        if ($this->middleware_handlers) {
            foreach ($this->middleware_handlers as $handler) {
                $filters = $handler->filters;
                $method = $handler->handler;
                // var_dump("匹配 middleware_handlers 消息： ", $filters->filters, $method, $filters->handler($update));
                if($filters->handler($update)) {
                    $state = call_user_func($method, $this, $update);
                    // 如果中间件 没有返回true 就停止处理
                    if (!$state) {
                        return;
                    }
                }
            }
        }

        if ($this->state_handler) {
            $state_type = $this->state_handler->state_type;
            $id = $state_type === StateHandler::CHAT_STATE ? $update->getChat()->id : (StateHandler::USER_STATE ?  $update->getFrom()->id : null);
            $state_data = $this->state_handler->get_state_date($id);
            // echo("\n{$id} 当前管理状态：{$state_data}  {$state_type}\n");

            // 如果没有开启状态先匹配下是否要开启状态
            if ($id && !$state_data) {
                foreach ($this->state_handler->entry_point_handlers as $handler) {
                    $filters = $handler->filters;
                    $method = $handler->handler;
                    var_dump("匹配 entry_point_handlers 消息： ", $filters->filters, $method, $filters->handler($update));
                    if($filters->handler($update)) {
                        $state = call_user_func($method, $this, $update);
                        var_dump("执行返回结果: {$state}");
                        return $this->state_handler->save_state_data($id, $state);
                    }
                }
            }

            // 如果已经开启了状态了
            if ($id && $state_data) {
                // 先匹配是否关闭的处理器
                foreach ($this->state_handler->fallback_handlers as $handler) {
                    $filters = $handler->filters;
                    $method = $handler->handler;
                    var_dump("匹配 fallback_handlers 消息： ", $filters->filters, $method, $filters->handler($update));
                    if($filters->handler($update)) {
                        $user_data[$update->getFromId()] = [];
                        $state = call_user_func($method, $this, $update);
                        var_dump("执行返回结果: {$state}");
                        return $this->state_handler->save_state_data($id, null);
                    }
                }

                // 在匹配状态的处理方法
                foreach ($this->state_handler->state_handlers[$state_data] as $handler) {
                    $filters = $handler->filters;
                    $method = $handler->handler;
                    var_dump("匹配 state_handlers 消息： ", $filters->filters, $method, $filters->handler($update));
                    if($filters->handler($update)) {
                        $user_data[$update->getFromId()] = [];
                        $state = call_user_func($method, $this, $update);
                        var_dump("执行返回结果: {$state}");
                        return $this->state_handler->save_state_data($id, $state);
                    }
                }
                // 保持状态
                return;
            }
        }
        
        // 最后在匹配普通的状态
        foreach ($this->handlers as $handler) {
            $filters = $handler->filters;
            $method = $handler->handler;
            // var_dump("匹配普通的状态: ", $filters->filters, $method, $filters->handler($update));
            if (($group === null || $group < $handler->group) && $filters->handler($update)) {
                $group = $handler->group;
                $result = call_user_func($method, $this, $update);
                if ($result) {
                    if ($id && $this->state_handler && $result === StateHandler::END) {
                        $this->state_handler->save_state_data($id, $result);
                    }
                    break;
                }
            }
        }
    }
}