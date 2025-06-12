<?php

namespace Telegram\Bot\Methods;

use Telegram\Bot\TelegramBotText;

/**
 * Class ReplyMessage.
 *
 * @mixin Http
 */
trait Text
{
    public ?TelegramBotText $text = null;

    public function setText(TelegramBotText $text) {
        $this->text = $text;
    }

    /**
     * 获取当前机器人的文本
     * @return string 返回文本
     */
    public function getText(string $lang, string $key, array $replacements=[]): string
    {
        if (!$this->text) {
            return $key;
        }

        return $this->text->get($lang, $key, $replacements);
    }
}
