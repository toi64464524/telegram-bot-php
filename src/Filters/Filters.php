<?php

namespace Telegram\Bot\Filters;

use Telegram\Bot\Exceptions\FilterException;
use Telegram\Bot\Types\Update;
use Telegram\Bot\Filters\Message;
use Telegram\Bot\Filters\Chat;

/**
 * Filters 类用于处理 Telegram Bot 的消息过滤器。
 * 它允许根据不同的条件（如消息类型、正则表达式等）来过滤和处理更新。
 */

class Filters
{  
    protected $map = [
        'all' => [Filters::class, 'all'], // 全部消息

        "regex" => [message::class, 'regex'], // 正则表达式匹配
        'message' => [message::class, 'message'], // 消息
        'text_message' => [message::class, 'text_message'], // 文本消息
        'photo_message' => [message::class, 'photo_message'], // 命令消息
        'command_message' => [message::class, 'command_message'], // 命令消息
        'private_chat_message' => [message::class, 'private_chat_message'], // 消息
        'group_chat_message' => [message::class, 'group_chat_message'], // 消息
        'inline_callback_message' => [message::class, 'inline_callback_message'], // 内联回调消息
        
        'private_chat' => [Chat::class, 'private_chat'], // 私聊消息
        'group_chat' => [Chat::class, 'group_chat'], // 群组消息
        'join_chat' => [Chat::class, 'join_chat'], // 进群消息
        'left_chat' => [Chat::class, 'left_chat'], // 退群消息
        'chat_right_changed' => [Chat::class, 'chat_right_changed'], // 群群聊权限变更
    ];

    private array $filters=[];

    public function __construct(array $filters=['all'])
    {
        $this->add($filters);
    }

    public function add(array $filters)
    {
        if ($this->filters) {
            array_push($this->filters, '&');
        }

        foreach($filters as $filter) {
            if (str_starts_with($filter, '!')) {
                array_push($this->filters, '!');
                $filter = trim($filter, '!');
            }

            $filter = trim($filter);
            if (@preg_match($filter, '') !== false) {
                array_push($this->filters, $filter);

            }else if (isset($this->map[strtolower($filter)]) || $filter !== '!' || $filter !== '&' || $filter !== '|') {

                array_push($this->filters, strtolower($filter));
            } else {
                throw new FilterException("{$filter} 不存在");
            }
        }
    }

    public function handler($update)
    {
        $reverse = false;
        $and = false;
        $or = false;
        $res = true;
        var_dump(json_encode($this->filters));
        foreach($this->filters as $filter) {
            if ($filter === '!') {
                $reverse = true;
            } else if ($filter === '&') {
                if (!$res) {
                    return false;
                }
                $and = true;
            } else if ($filter === '|') {
                $or = true;
                $and = false;
            } else {
                if (@preg_match($filter, '') !== false) {
                    $result = call_user_func($this->map['regex'], $update, trim($filter));
                } else {
                    $result = call_user_func($this->map[$filter], $update);
                }

                if ($reverse) {
                    $result = !$result; // 反转逻辑
                }

                if ($or && (!$result && !$res)) {
                    return false;
                } else if (!$result && !$res) {
                    return false;
                }

                $res = $result;
                $reverse = false;
                $and = true;
                $or = false;
            }
        }
        
        return $res;
    }

    public static function all(Update $update): bool
    {
        return true;
    }
}