<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class StateHandler
{
    const USER_STATE = 'user';
    const CHAT_STATE = 'chat';
    const END = -1;

    public array $entry_point_handlers;
    public array $state_handlers;
    public array $fallback_handlers;
    public array $state_data;
    public string $state_type;

    public function  __construct(array $entry_points, array $states, array $fallback_handlers, string $state_type=self::CHAT_STATE) {
        $this->entry_point_handlers =[];
        $this->state_handlers =[];
        $this->fallback_handlers =[];
        $this->state_data = []; // 存储状态

        if ($state_type !== self::USER_STATE && $state_type !== self::CHAT_STATE) {
            throw new \Exception('缓存类型错误');
        }

        $this->state_type = $state_type;

        foreach($entry_points as $handler) {
            if ($handler instanceof MessageHandler or $handler instanceof InlineCallbackHandler or $handler instanceof CommandHandler or $handler instanceof KeyboardHandler) {
                $handler->group = 0;
                array_push($this->entry_point_handlers, $handler);
            }
        }

        foreach($states as $key => $handlers) {
            $this->state_handlers[$key] = [];
            foreach($handlers as $handler) {
                if ($handler instanceof MessageHandler or $handler instanceof InlineCallbackHandler or $handler instanceof CommandHandler or $handler instanceof KeyboardHandler) {
                    $handler->group = 1;
                    array_push($this->state_handlers[$key], $handler);
                }
            }
        }

        foreach($fallback_handlers as $handler) {
            if ($handler instanceof MessageHandler or $handler instanceof InlineCallbackHandler or $handler instanceof CommandHandler or $handler instanceof KeyboardHandler) {
                $handler->group = 3;
                array_push($this->fallback_handlers, $handler);
            }
        }
    }

    /**
     * 获取状态处理器
     * @param string $state 状态名称
     * @return array|null 返回状态状态或null
     */
    public function get_user_state($id): string|null
    {
        if (isset($this->state_data[$id])){
            return $this->state_data[$id];
        }
        return null;
    }

    /**
     * 保存状态数据
     * @param int $id 用户或聊天ID
     * @param string|null $state 状态名称或结束状态
     * @return bool 成功返回true，失败返回false
     */
    public function set_user_state(int $id, string $state=null): bool
    {
        if ($state === self::END && isset($this->state_data[$id])) {
            unset($this->state_data[$id]);
        } else if (is_string($state) && isset($this->state_handlers[$state])) {
            $this->state_data[$id] = $state;
        } else {
            if (isset($this->state_data[$id])) {
                unset($this->state_data[$id]);
            }
        }
        return true;
    }

    public function merge(StateHandler $handler): StateHandler
    {
        array_merge($this->entry_point_handlers, $handler->entry_point_handlers);
        array_merge($this->state_handlers, $handler->state_handlers);
        array_merge($this->fallback_handlers, $handler->fallback_handlers);
        return $this;
    }

}