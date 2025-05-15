<?php
namespace telegram\bot\types;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message as BaseMessage;
use Illuminate\Support\Collection;

class Message extends BaseMessage
{
    public Api $telegram;

    public function setBot(Api $bot)
    {
        $this->telegram = $bot;
        return $this;
    }

    /**
     * 判断消息类型是否为某种类型
     */
    public function isType(string $type): bool
    {
        if ($this->has(strtolower($type))) {
            return true;
        }
        return false;
    }

    public function getId(): int
    {
        return $this->getMessage()->message_id;
    }

    public function getChatId(): int
    {
        return $this->getChat()->id;
    }

    public function getFromId(): int
    {
        return $this->getFrom()->id;
    }

    public function getText() : string
    {
        return $this->getMessage()->text;
    }

    /**
     * 获取 message 类型（文本、图片、视频等）
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

    public function reply(string $text, array $options = []): ?BaseMessage
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
