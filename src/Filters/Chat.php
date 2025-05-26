<?php

namespace Telegram\Bot\Filters;

use Telegram\Bot\Exceptions\FilterException;
use Telegram\Bot\Types\Update;
/**
 * Filters 类用于处理 Telegram Bot 的消息过滤器。
 * 它允许根据不同的条件（如消息类型、正则表达式等）来过滤和处理更新。
 */

class Chat
{  
    /**
     * 检查更新是否为私聊消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是私聊消息返回 true，否则返回 false
     */
    public static function private_chat(Update $update): bool
    {
        if ($update->getChat()->type === 'private') {
            return false;
        }
        return true;
    }

    /**
     * 检查更新是否为群组消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是群组消息返回 true，否则返回 false
     */
    public static function group_chat(Update $update): bool
    {
        if ($update->getChat()->type !== 'private') {
            return false;
        }
        return true;
    }

    /**
     * 检查更新是否为进群消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function join_chat(Update $update): bool
    {
        if (!$update->isType('my_chat_member')) {
            return false;
        }

        if ($update->getMyChatMember()->newChatMember->status === 'member') {
            return true;
        }

        return false;
    }

    /**
     * 检查更新是否为退出群消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function left_chat(Update $update): bool
    {
        if (!$update->isType('my_chat_member')) {
            return false;
        }

        if ($update->getMyChatMember()->newChatMember->status === 'left' || $update->getMyChatMember()->newChatMember->status === 'kicked') {
            return true;
        }
        
        return false;
    }

    /**
     * 检查更新是否为群组新管理员事件。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function chat_right_changed(Update $update): bool
    {
        if (!$update->isType('my_chat_member')) {
            return false;
        }
        if ($update->getMyChatMember()->newChatMember->status === 'administrator' || $update->getMyChatMember()->newChatMember->status === 'restricted') {
            return true;
        }
        
        return false;
    }
}