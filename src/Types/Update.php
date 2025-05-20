<?php
namespace Telegram\Bot\Types;

use Telegram\Bot\Objects\Update as BaseUpdate;
use Illuminate\Support\Collection;
use Telegram\Bot\Api;

class Update extends BaseUpdate
{
    public Api $telegram;

    public function getId() :int
    {
        return $this->updateId;
    }

    /**
     * 获取当前 Update 的类型
     */
    public function getType(): string
    {
        $types = [
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'inline_query',
            'chosen_inline_result',
            'callback_query',
            'shipping_query',
            'pre_checkout_query',
            'poll',
        ];

        foreach ($types as $type) {
            if ($this->has($type)) {
                return $type;
            }
        }

        return 'unknown';
    }

    public function setBot(Api $bot)
    {
        $this->telegram = $bot;
    }

    public function getChatId(): int
    {
        return $this->getChat()->id;
    }

    public function getFromId(): int
    {
        return $this->getFrom()->id;
    }

    public function getFrom() : Collection
    {
        if ($this->isType('callback_query')) {
            return $this->getCallbackQuery()->getFrom();
        } else if ($this->isType('message')) {
            return $this->getMessage()->getFrom();
        }
        return new Collection();
    }

    /**
     * 返回数组 数组中有 command 和 args[]
     * 
     */
    public function getCallbackData() : CallbackData
    {
        return new CallbackData($this->getCallbackQuery());
    }

    public function getMessage() : Collection
    {
        $raw = match ($this->getType()) {
            'message'              => $this->message,
            'edited_message'       => $this->editedMessage,
            'channel_post'         => $this->channelPost,
            'edited_channel_post'  => $this->editedChannelPost,
            'callback_query'       => $this->callbackQuery?->message ?? null,
            default                => null,
        };

        if ($raw && $this->telegram) {
            return (new Message($raw))->setBot($this->telegram);
        }

        return new Collection();
    }
}