<?php

namespace Telegram\Bot\Objects;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Resource extends Collection
{
    protected string $language;

    public function __construct($language, array $data)
    {
        parent::__construct($data);
        $this->language = strtolower($language);
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getText(string $key, $replace=[])
    {
        $data = $this->get($key, []);
        if (isset($data['text'])) {
            foreach ($replace as $k => $v) {
                $data['text'] = str_replace("{{{$k}}}", $v, $data['text']);
            }
            $data['text'] = str_replace('\n', "\n", $data['text']);
            return  $data['text'];
        }
        return $key;
    }

    public function getTyep($key)
    {
        $data = $this->get($key, []);
        if (isset($data['file_id'])) {
            return  $data['file_id'];
        }
        return 'text';
    }

    public function getFileId(string $key)
    {
        $data = $this->get($key, []);
        if (isset($data['file_id'])) {
            return  $data['file_id'];
        }
        return null;
    }
}