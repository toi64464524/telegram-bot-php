<?php

namespace telegram\bot\types;

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

    public function  __construct(array $entry_points, array $states, array $fallback_handlers, string $state_type=null) {
        $this->entry_point_handlers =[];
        $this->state_handlers =[];
        $this->fallback_handlers =[];
        $this->state_data = [];

        if ($state_type && $state_type !== self::USER_STATE && $state_type !== self::CHAT_STATE) {
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

    public function get_state_date($id): string|null
    {
        if (isset($this->state_data[$id])){
            return $this->state_data[$id];
        }
        return null;
    }

    public function save_state_data(int $id, $state=null)
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

    }
}