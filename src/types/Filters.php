<?php

namespace telegram\bot\types;

use Exception;

class Filters
{  
    protected $map = [
        'all' => [Filters::class, 'all'],
        'is_message' => [Filters::class, 'is_message'],
        'is_inline_callback' => [Filters::class, 'is_inline_callback'],
        "regex" => [Filters::class, 'regex'],
        'is_command' => [Filters::class, 'is_command'],
        'is_private_chat' => [Filters::class, 'is_private_chat'],
    ];

    public array $filters;

    public function __construct(array $filters=['all'])
    {
        $this->filters = [];
        $this->add($filters);
    }

    public function add(array $filters)
    {
        foreach($filters as $filter) {
            $is_reverse = false;
            $filter = trim($filter);
            $key = strtolower(trim($filter));
            if (str_starts_with($filter, '~')) {
                $filter = substr($filter, 1);  // 去除'~'字符
                $is_reverse = true;
            }

            if (@preg_match($filter, '') !== false) {
                array_push($this->filters, ['key' => 'regex', 'is_reverse' => $is_reverse, 'method' => $this->map['regex'], 'pattern' => $key]);
            } else if (isset($this->map[$key]) && method_exists($this->map[$key][0], $this->map[$key][1])) {
                array_push($this->filters, ['key' => $key, 'is_reverse' => $is_reverse, 'method' => $this->map[$key]]);
            }else{
                throw new \Exception("{$filter} 不存在");
            }
        }
    }

    public function handler($update)
    {
        foreach($this->filters as $filter) {
            var_dump($filter);
            $is_reverse = $filter['is_reverse'];
            if ($filter['key'] === 'regex') {
                $result = call_user_func($filter['method'], $update, $filter['pattern']);
            } else {
                $result = call_user_func($filter['method'], $update);
            }
            var_dump($result);
            if ($is_reverse === $result) {
                return false;
            }
        }
        
        return true;
    }

    public static function all(Update $update): bool
    {
        return true;
    }

    public static function is_private_chat(Update $update): bool
    {
        if ($update->getChat()->type !== 'private') {
            return false;
        }
        return true;
    }

    public static function is_command(Update $update): bool
    {
        if ($update->isType('message') && str_starts_with($update->getMessage()->text, '/')) {
            return true;
        }
        return false;
    }

    public static function is_message(Update $update): bool
    {
        if ($update->isType('message')) {
            return true;
        }
        return false;
    }

    public static function is_inline_callback(Update $update): bool
    {
        if ($update->isType('callback_query')) {
            return true;
        }
        return false;
    }

    public static function regex(Update $update, string $pattern): bool
    {
        // var_dump("regex 判断：", preg_match($pattern, $update->getMessage()->text), $pattern, $update->getMessage()->text);
        if ($update->isType('callback_query') && preg_match($pattern, $update->getCallbackQuery()->data)) {
            return true;
        }else if ($update->isType('message') && preg_match($pattern, $update->getMessage()->text)) {
            return true;
        }
        return false;
    }
}