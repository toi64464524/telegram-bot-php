<?php
namespace Telegram\Bot\Types;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message as BaseMessage;

class Message extends BaseMessage
{
    public Api $telegram;

    /**
     * 设置 Bot 实例
     * @param Api $bot Telegram Bot 实例
     * @return $this 返回当前实例以支持链式调用
     */
    public function setBot(Api $bot)
    {
        $this->telegram = $bot;
        return $this;
    }

    /**
     * 判断消息类型是否为某种类型
     * @param string $type 消息类型
     * @return bool 如果消息类型匹配则返回 true，否则返回 false
     */
    public function isType(string $type): bool
    {
        if ($this->has(strtolower($type))) {
            return true;
        }
        return false;
    }

    /**
     * 获取消息 ID
     * @return int 返回消息 ID
     */
    public function getId(): int
    {
        return $this->message_id;
    }

    /**
     * 获取消息发送的聊天对象
     * @return Chat 返回聊天对象
     */
    public function getChatId(): int
    {
        return $this->getChat()->id;
    }

    /**
     * 获取消息发送者信息
     * @return User 返回发送者对象
     */
    public function getFromId(): int
    {
        return $this->getFrom()->id;
    }

    /**
     * 获取消息文本内容
     * @return string 返回消息文本
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * 获取 message 类型（文本、图片、视频等）
     * @return string 返回消息类型
     */
    public function getType(): string
    {
        $types = [
            'text',
            'photo',
            'video',
            'voice',
            'sticker',
            'animation',
            'document',
            'audio',
            'contact',
            'location',
            'venue',
            'poll',
            'dice',
        ];

        foreach ($types as $type) {
            if ($this->has($type)) {
                return $type;
            }
        }

        return 'unknown';
    }

    /**
     * 回复消息
     * @param InputMessage $message 输入消息对象
     * @return BaseMessage|null
     * @throws \RuntimeException 如果未设置 Bot 实例
     */
    public function reply(InputMessage $message): ?BaseMessage
    {
        if (!$this->telegram) {
            throw new \RuntimeException('Bot instance not set in Message.');
        }
        $message->setParam('chat_id', $this->chat->id);
        $message->setParam('reply_to_message_id', $this->messageId);
        return $this->telegram->sendMessage($message->make($this->chat->id));
    }

    /**
     * 回复文本消息
     * @param string $text 文本内容
     * @param array $options 可选参数
     * @return BaseMessage|null
     * @throws \RuntimeException 如果未设置 Bot 实例
     * @throws \InvalidArgumentException 如果文本内容无效
     */
    public function reply_text(string $text, array $options = []): ?BaseMessage
    {
        if (!$this->telegram) {
            throw new \RuntimeException('Bot instance not set in Message.');
        }

        return $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->chat->id,
            'text' => $text,
            'reply_to_message_id' => $this->messageId,
        ], $options));
    }

    /**
     * 回复图片消息
     * @param string $photo 图片文件路径或URL
     * @param array $options 可选参数
     * @return BaseMessage|null
     * @throws \RuntimeException 如果未设置 Bot 实例
     * @throws \InvalidArgumentException 如果图片路径无效
     */
    public function reply_photos(string $photo, array $options = []): ?BaseMessage
    {
        if (!$this->telegram) {
            throw new \RuntimeException('Bot instance not set in Message.');
        }

        return $this->telegram->sendMessage(array_merge([
            'chat_id' => $this->chat->id,
            'photos' => fread($photo, 'r'),
            'reply_to_message_id' => $this->messageId,
        ], $options));
    }

    public function delete(): bool
    {
        if (!$this->telegram) {
            throw new \RuntimeException('Bot instance not set in Message.');
        }

        return $this->telegram->deleteMessage([
            'chat_id' => $this->chat->id,
            'message_id' => $this->messageId,
        ]);
    }
}
