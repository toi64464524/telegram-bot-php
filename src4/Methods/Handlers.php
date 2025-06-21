<?php

namespace Telegram\Bot\Methods;

use Telegram\Bot\TelegramBotHandlers;
use Telegram\Bot\TelegramBotText;


/**
 * Class ReplyMessage.
 *
 * @mixin Http
 */
trait Handlers
{
    private int $id;
    private string $token;
    // public ?TelegramBotText $text = null;
    public ?TelegramBotHandlers $handlers=null;
    
    public function init() {}

    /**
     * 运行机器人
     * @param array $options 选项数组
     * @return void
     * @throws \Exception
     */
    public function run(array $options=[])
    {
        $update_id = 1;
        $this->init();
        empty($options['limit']) && $options['limit'] = 100;
        empty($options['offset']) && $options['offset'] = 1;

        while($this->handlers) {
            try {
                $options['offset'] = $update_id;
                $updates = $this->getUpdates($options);
                if($updates) {
                    foreach ($updates as $update) {
                        echo time() . "开始处理update ID: {$update->update_id}\n";
                        $update_id = $update->update_id + 1;
                        go(function () use ($update) {
                            $this->handlers->match($this, $update);
                            echo time() . "处理结束update ID: {$update->update_id}\n";
                        });
                        
                    }
                }
            } catch (\Exception $e) {
                echo "{$e->getMessage()}";
                var_dump($e->getMessage());
            }
        }
    }

    public function setHandler(TelegramBotHandlers $handlers) {
        $this->handlers = $handlers;
    }

    // 设置缓存数据
    public function setUserData(int $id, string $key, $value): bool 
    {
        if ($this->handlers) {
            return $this->handlers->setUserData($id, $key, $value);
        }
        return false;
    }

    // 获取缓存数据
    public function getUserData(int $id, string $key)
    {
        if ($this->handlers) {
            return $this->handlers->getUserData($id, $key);
        }
        return null;
    }

    // 获取上一次发送的消息
    public function getLastMessage(int $id) :?Message
    {
        if (isset($this->handlers->chat_last_message[$id])) {
            $message = $this->chat_last_message[$id];
            $message->setTelegram($this);
        }

        return null;
    }
}
