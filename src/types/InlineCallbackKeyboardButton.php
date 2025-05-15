<?php

namespace telegram\bot\types;

class InlineCallbackKeyboardButton {
    public array $data;
    public function __construct(string $text, array $params=[])
    {
        $this->data =['text' => $text];

        if(isset($params['url'])) {
            if (is_string($params['url'])){
                $this->data['url'] = $params['url'];
            }
            throw new \Exception("url 只能是字符串");

        } else if(isset($params['callback_data'])) {
            if (is_string($params['callback_data'])){
                $this->data['callback_data'] = $params['callback_data'];
            }
            throw new \Exception("callback_data 只能是字符串");

        } else if(isset($params['login_url'])) {
            if (is_array($params['login_url'])) {
                $this->data['login_url'] = $params['login_url'];
            }
            throw new \Exception("login_url 只能是数组");

        } else if(isset($params['switch_inline_query'])) {
            if (is_string($params['switch_inline_query'])) {
                $this->data['switch_inline_query'] = $params['switch_inline_query'];
            }
            throw new \Exception("switch_inline_query 只能是字符串");

        } else if(isset($params['switch_inline_query_current_chat'])) {
            if (is_string($params['switch_inline_query_current_chat'])){
                $this->data['switch_inline_query_current_chat'] = $params['switch_inline_query_current_chat'];
            }
            throw new \Exception("switch_inline_query_current_chat 只能是字符串");

        } else if(isset($params['web_app'])) {
            if (is_array($params['web_app'])){
                $this->data['web_app'] = $params['web_app'];
            }
            throw new \Exception("web_app 只能是数组");
        } else if(isset($params['switch_inline_query_chosen_chat'])) {
            if (is_array($params['switch_inline_query_chosen_chat'])){
                $this->data['switch_inline_query_chosen_chat'] = $params['switch_inline_query_chosen_chat'];
            }
            throw new \Exception("switch_inline_query_chosen_chat 只能是数组");
        } else if(isset($params['copy_text'])) {
            if (is_array($params['copy_text'])){
                $this->data['copy_text'] = $params['copy_text'];
            }
            throw new \Exception("copy_text 只能是数组");
        } else if(isset($params['callback_game'])) {
            if (is_array($params['callback_game'])){
                $this->data['callback_game'] = $params['callback_game'];
            }
            throw new \Exception("callback_game 只能是数组");
        } else if(isset($params['pay'])) {
            if (is_bool($params['pay'])){
                $this->data['pay'] = $params['pay'];
            }
            throw new \Exception("pay 只能是bool");
        } else {
            throw new \Exception("参数错误");
        }
    }

    public function delete()
    {
        $this->data = [];
    }
}