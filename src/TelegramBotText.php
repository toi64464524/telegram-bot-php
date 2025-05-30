<?php

namespace Telegram\Bot;

// use Illuminate\Support\Facades\Redis;

/**
 * TelegramBotText
 *
 * 这是一个用于处理Telegram Bot文本的类，支持多语言文本存储和获取。
 */
class TelegramBotText
{
    protected int $telegram_bot_id;
    protected array $langs = [];
    protected array $texts = [];
    /**
     * 初始化文本数据
     *
     * @param array $texts ["en" = [文本数据数组]]
     * @example $texts = ['en' => ['key1' => '文本1', 'key2' => '文本2'], 'zh' => ['key1' => '文本1', 'key2' => '文本2']]
     */
    public function __construct(int $telegram_bot_id, array $texts = [])
    {
        $this->telegram_bot_id = $telegram_bot_id;
        // 如果有传入文本数据，则初始化
        $this->langs = array_map('strtolower', array_keys($texts));

        foreach ($texts as $lang => $texts) {
            $this->addLang(strtolower($lang), $texts);
        }
    }

    /**
     * 设置指定语言和键的文本
     * @param string $lang 语言代码
     * @param array $texts 文本数据数组
     * @example $texts = ['key1' => '文本1', 'key2' => '文本2']
     * @return array 返回支持的语言列表
     */
    public function addLang(string $lang, array $texts)
    {
        $lang = strtolower($lang);
        $this->langs[$lang] = $texts;
    }

    /**
     * 获取指定语言和键的文本
     *
     * @param string $lang 语言代码
     * @param string $key 键名
     * @param array $replacements 替换占位符的值
     * @return string 返回处理后的文本
     */
    public function get($lang, $key, $replacements=[])
    {
        $lang = strtolower($lang);
        if (!isset($this->langs[$lang])) {
            $lang = $key; // 默认语言
        }

        if (!isset($this->langs[$lang][$key])) {
            return $key; // 如果没有找到对应的文本，返回键名
        }

        $text = $this->langs[$lang][$key];

        // 如果没有替换值，直接返回文本
        if (empty($replacements)) {
            return $text;
        }

        // 替换占位符
        $formattedReplacements = [];
        foreach ($replacements as $key => $value) {
            $formattedReplacements["{{{$key}}}"] = $value;
        }

        $text = str_replace('\n', "\n", $text);
        return strtr($text, $formattedReplacements);
    }
}