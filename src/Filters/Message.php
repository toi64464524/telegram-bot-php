<?php

namespace Telegram\Bot\Filters;

use Telegram\Bot\Exceptions\FilterException;
use Telegram\Bot\Types\Update;
/**
 * Filters 类用于处理 Telegram Bot 的消息过滤器。
 * 它允许根据不同的条件（如消息类型、正则表达式等）来过滤和处理更新。
 */

class Message
{  
    /**
     * 检查更新是否为命令消息。
     */
    public static function command_message(Update $update): bool
    {
        if ($update->isType('message') && str_starts_with($update->getMessage()->getText(), '/')) {
            return true;
        }
        
        return false;
        
    }
    /**
     * 检查更新是否为私聊消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是私聊消息返回 true，否则返回 false
     */
    public static function private_chat_message(Update $update): bool
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
    public static function group_chat_message(Update $update): bool
    {
        if ($update->getChat()->type !== 'private') {
            return false;
        }
        return true;
    }

    /**
     * 检查更新是否为消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function message(Update $update): bool
    {
        if ($update->isType('message')) {
            return true;
        }
        return false;
    }

    /**
     * 检查更新是否为文本消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function text_message(Update $update): bool
    {
        if ($update->isType('message') && $update->getMessage()->hasText()) {
            return true;
        }
        return false;
    }

    /**
     * 检查更新是否为图片消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function photo_message(Update $update): bool
    {
        if ($update->isType('message') && $update->getMessage()->hasPhoto()) {
            return true;
        }
        return false;
    }

    /**
     * 检查更新是否为内联回调消息。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function inline_callback_message(Update $update): bool
    {
        if ($update->isType('callback_query')) {
            return true;
        }
        return false;
    }

    /**
     * 检查更新是消息文件正则。
     *
     * @param Update $update Telegram 更新对象
     * @return bool 如果是命令消息返回 true，否则返回 false
     */
    public static function regex(Update $update, string $pattern): bool
    {
        if ($update->isType('callback_query') && preg_match($pattern, $update->getCallbackQuery()->data)) {
            return true;
        }else if ($update->isType('message') && preg_match($pattern, $update->getMessage()->text)) {
            return true;
        }
        return false;
    }
}