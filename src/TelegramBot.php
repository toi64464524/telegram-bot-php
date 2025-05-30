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
use Telegram\Bot\Handlers\Handler;
use Telegram\Bot\Handlers\MessageHandler;
use Telegram\Bot\Handlers\MessageHandlers;
use Telegram\Bot\Handlers\KeyboardHandler;
use Telegram\Bot\Handlers\KeyboardHandlers;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramBot extends Api
{
    // 中间件控制器 最先执行
    public array $middleware_handlers=[];
    // 命令控制器
    public array $command_handlers=[];
    // 状态控制器
    public ?StateHandler $state_handler= null;
    // 普通处理器
    public array $handlers=[];
    // 窗口数据缓存
    private array $chat_data= [];
    // 用户数据缓存
    private array $user_data= [];
    private int $max_update_id=0;
    private int $id;
    private string $token;
    private ?TelegramBotText $text = null;

    public function __construct(string $token, bool $async = false, $httpClientHandler = null, string $baseBotUrl = null)
    {
        parent::__construct($token, $async, $httpClientHandler, $baseBotUrl);
        $this->id = (int) explode(":", $token)[0];
        $this->token = $token;
    }

    /**
     * 初始化机器人
     * 让子类实现自己的初始化逻辑
     */
    public function init() {}

    /**
     * 设置状态处理器
     * 让子类实现自己的状态处理器逻辑
     */
    public function setStateHandler(): ?StateHandler {
        return null;
    }
    
    /**
     * 设置消息处理器
     * 让子类实现自己的消息处理器逻辑
     */
    public function setHandler(): ?Handlers{
        return null;
    }

    /**
     * 获取当前机器人的文本
     * @return TelegramBotText 返回文本对象
     */
    public function setText(string $lang, array $text)
    {
        if (!$this->text) {
            $this->text = new TelegramBotText($this->id);
        }
        $this->text->addLang($lang, $text);
        return $this->text;
    }

    /**
     * 获取当前机器人的文本
     * @return TelegramBotText 返回文本对象
     */
    public function getText(string $lang, string $key, array $replacements): string
    {
        if (!$this->text) {
            return $key;
        }

        return $this->text->get($lang, $key, $replacements);
    }
    
    /**
     * 设置窗口数据缓存
     * @param int $chat_id 聊天ID
     * @param string $key 缓存键
     * @param mixed $data 缓存数据
     * @return bool 返回是否设置成功
     */
    public function setChatData(int $chat_id, string $key, mixed $data) :bool
    {
        $this->chat_data[$chat_id] = $data;
        return true;
    }

    /**
     * 获取聊天数据缓存
     * @param int $chat_id 聊天ID
     * @param string $key 缓存键
     * @param mixed $data 缓存数据
     * @return array 返回聊天数据
     */
    public function getChatData(int $chat_id, string $key, mixed $data): mixed
    {
        return $this->chat_data[$chat_id] ?? null;
    }

    /**
     * 设置用户数据缓存
     * @param int $user_id 用户ID
     * @param string $key 缓存键
     * @param mixed $data 缓存数据
     * @return bool 返回是否设置成功
     */
    public function setUserData(int $user_id, string $key, mixed $data): bool
    {
        $this->user_data[$user_id] = $data;
        return true;
    }

    /**
     * 获取用户数据缓存
     * @param int $user_id 用户ID
     * @param string $key 缓存键
     * @return array 返回用户数据
     */
    public function getUserData(int $user_id): mixed
    {
        return $this->user_data[$user_id] ?? null;
    }

    /**
     * 获取当前机器人的最大更新ID
     * @return int 返回最大更新ID
     */
    protected function getMaxUpdateId(): int
    {
        return $this->max_update_id;
    }

    /**
     * 获取当前机器人的ID
     * @return int 返回机器人的ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 获取当前机器人的Token
     * @return string 返回机器人的Token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * 发送消息到指定的聊天
     * @param int $chat_id 聊天ID
     * @param InputMessage $message 消息内容
     * @return mixed 返回发送消息的结果
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function send_message(InputMessage $message)
    {
        $params = $message->make($message->getChatId());
        return parent::sendMessage($params);
    }

    /**
     * 编辑指定聊天中的消息
     * @param int $chat_id 聊天ID
     * @param int $message_id 消息ID
     * @param array $params 编辑参数
     * @return mixed 返回编辑消息的结果
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function edit_message(int $chat_id, int $message_id, array $params)
    {
        $params['chat_id'] = $chat_id;
        $params['message_id'] = $message_id;

        parent::editMessage(($params));
    }

    /**
     * 删除指定聊天中的消息
     * @param int $chat_id 聊天ID
     * @param int $message_id 消息ID
     * @return void
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
    public function delete_message(int $chat_id, int $message_id)
    {
        $params = [
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ];
        parent::deleteMessage($params);
    }

    // protected function send_message(string|int $chat_id, array $params, InlineCallbackKeyboardMarkup|KeyboardMarkup $markup=null)
    // {
    //     $params['chat_id'] = $chat_id;
    //     $params['parse_mode'] = isset($params['parse_mode']) ? $params['parse_mode'] : 'HTML';
    //     if ($markup){
    //         $params['reply_markup'] = $markup->make();
    //     }

    //     return parent::sendMessage($params);
    // }

    /**
     * 发送文本消息到指定的聊天
     * @param string|int $chat_id 聊天ID
     * @param string $text 消息文本
     * @param array $params 附加参数
     * @param InlineCallbackKeyboardMarkup|KeyboardMarkup|null $markup 键盘标记
     * @return mixed 返回发送消息的结果
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
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

    // 添加处理器
    protected function add_handler(StateHandler|Handler $handler)
    {
        if ($handler instanceof CommandHandler) {
            array_push($this->command_handlers, $handler);

        } else if ($handler instanceof KeyboardHandler) {
            array_push($this->command_handlers, $handler);

        } else if ($handler instanceof InlineCallbackHandler) {
            array_push($this->handlers, $handler);

        } else if ($handler instanceof MessageHandler) {
            array_push($this->handlers, $handler);

        } else if ($handler instanceof Handler) {
            array_push($this->handlers, $handler);

        } else if ($handler instanceof MiddlewareHandler) {
            array_push($this->middleware_handlers, $handler);

        } else if ($handler instanceof StateHandler) {
            $this->state_handler = $handler;
        }

        usort($this->middleware_handlers, function($a, $b) {
            return $a->getGroup() <=> $b->getGroup();
        });

        usort($this->handlers, function($a, $b) {
            return $a->getGroup() <=> $b->getGroup(); 
        });
    }

    // 批量添加处理器
    protected function add_handlers(Handlers $handlers)
    {
        foreach ($handlers as $handler) {
            $this->add_handler($handler);
        }
    }
    
    /**
     * 获取更新列表
     * @param array $options 选项数组
     * @param bool $shouldDispatchEvents 是否分发事件
     * @return Update[] 返回更新列表
     * @throws \Telegram\Bot\Exceptions\TelegramSDKException
     */
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

    /**
     * 运行机器人
     * @param array $options 选项数组
     * @return void
     * @throws \Exception
     */
    public function run(array $options=[])
    {
        $state_handler = $this->setStateHandler();
        $handler = $this->setHandler();
        $state_handler && $this->add_handler($state_handler);
        $handler && $this->add_handlers($handler);
        $this->init();

        empty($options['limit']) && $options['limit'] = 100;
        empty($options['offset']) && $options['offset'] = 1;
        while(True) {
            try {
                $options['offset'] = $this->max_update_id + 1;
                foreach ($this->getUpdates($options) as $update) {
                    $this->max_update_id = $update->update_id;
                    $this->exec_handlers($update);
                }
            } catch (\Exception $e) {
                echo "{$e->getMessage()}";
                var_dump($e->getMessage());
            }
            
        }
    }

    /**
     * 执行处理器
     * @param Update $update 更新对象
     * @return void
     * @throws \Exception
     */
    private function exec_handlers(Update $update)
    {
        $state_id = null;
        if ($this->state_handler) {
            $state_type = $this->state_handler->state_type;
            $state_id = $state_type === StateHandler::CHAT_STATE ? $update->getChat()->id : (StateHandler::USER_STATE ?  $update->getFrom()->id : null);
        }
        
        // 中间件控制器
        var_dump("匹配中间件控制器");
        $state = $this->handler_handlers($update, $this->middleware_handlers, $state_id);
        if ($state) {
            return;
        }

        // 命令控制器
        var_dump("匹配命令控制器");
        $state = $this->handler_handlers($update, $this->command_handlers, $state_id);
        if ($state) {
            return;
        }

        // 状态控制器
        if ($state_id) {
            var_dump("匹配状态控制器");
            $state_data = $this->state_handler->get_state_date($state_id);
            // 如果没有开启状态先匹配下 是否要开启状态
            if (!$state_data) {
                $state = $this->handler_handlers($update, $this->state_handler->entry_point_handlers, $state_id);
                if ($state) {
                    return $this->state_handler->save_state_data($state_id, $state);
                }
            } else if ($state_data) { // 如果已经开启了状态了
                // 先匹配是否关闭的处理器
                $state = $this->handler_handlers($update, $this->state_handler->fallback_handlers, $state_id);
                if ($state) {
                    return $this->state_handler->save_state_data($state_id, null);
                }

                $state_handlers = $this->state_handler->state_handlers[$state_data];
                // 在匹配状态的处理方法
                $state = $this->handler_handlers($update, $state_handlers, $state_id);
                if ($state) {
                    return $this->state_handler->save_state_data($state_id, $state);
                }
                // 停止普通方法的匹配
                return null;
            }
        }

        var_dump("匹配普通控制器");
        // 最后在匹配普通的状态
        $state = $this->handler_handlers($update, $this->handlers, $state_id);
        if ($state) {
            return $this->state_handler->save_state_data($state_id, $state);
        }
    }

    /**
     * 循环匹配处理器
     * @param Update $update 更新对象
     * @param array $handlers 处理器数组
     * @param int|null $state_id 状态ID
     * @return mixed 返回状态字符串 或控制器执行结果 或 null 没有匹配到或者需要继续执行都返回null
     * @throws \Exception
     */
    private function handler_handlers(Update $update, array $handlers, int $state_id=null)
    {
        foreach ($handlers as $handler) {
            $filters = $handler->getFilters();
            $method = $handler->getHandler();
            // 如果没有设置过滤器，直接执行
            if($filters->handler($update)) {
                $state = call_user_func($method, $this, $update);
                if ($state!==null) {
                    // 如果返回值是字符串，表示状态
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