<?php

namespace Telegram\Bot\Methods;

use Telegram\Bot\Objects\Resource;

/**
 * Class ReplyMessage.
 *
 * @mixin Http
 */
trait StaticResouce
{
    private ?array $resouces = [];

    public function setResource(string $lang, Resource $resouce) {
        $lang = strtolower($lang);
        $this->resouces[$lang] = $resouce;
    }

    /**
     * 获取机器人支持语言
     */
    public function getLanguages(): array
    {
        return array_keys($this->resouces);
    }

    /**
     * 获取当前机器人的文本
     * @return string 返回文本
     */
    public function getResource(string $lang): ?Resource
    {
        $lang = strtolower($lang);
        if (!isset($this->resouces[$lang])) {
            throw new \Exception("不支持语言{$lang}");
        }

        return $this->resouces[$lang];
    }
}
