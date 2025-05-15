<?php

namespace App\Bot;

use App\Models\TelegramBotText as TextModel;
use Illuminate\Support\Facades\Redis;

class TelegramBotText
{
    protected int $telegram_bot_id;
    protected TextModel $model;

    public function __construct(int $telegram_bot_id)
    {
        $texts = TextModel::where('telegram_bot_id', $telegram_bot_id)->get();
        $this->telegram_bot_id = $telegram_bot_id;

        // 分组处理
        $grouped = $texts->groupBy(function ($item) {
            return "bot:{$item->telegram_bot_id}:lang:{$item->lang}";
        });

        // 存入 Redis
        foreach ($grouped as $redisKey => $items) {
            $hash = [];

            foreach ($items as $config) {
                $hash[$config->key] = $config->value;
            }

            // 批量写入 hash
            Redis::hmset($redisKey, $hash);
        }
    }

    public function get($lang, $key, $replacements=[])
    {
        $lang = strtolower($lang);

        $text = Redis::hget("bot:{$this->telegram_bot_id}:lang:{$lang}", $key);
        if (!$text && $lang!=='zh-hans') {
            $text = Redis::hget("bot:{$this->telegram_bot_id}:lang:{$lang}", $key);
        }

        if (!$text) {
            return $key;
        }else{
            $formattedReplacements = [];
            foreach ($replacements as $key => $value) {
                $formattedReplacements["{{{$key}}}"] = $value;
            }
            $text = str_replace('\n', "\n", $text);
            return strtr($text, $formattedReplacements);
        }
    }
}