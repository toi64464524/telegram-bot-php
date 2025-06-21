<?php

namespace Telegram\Bot\Markups;

class KeyboardButton 
{
    public array $data;

    public function __construct(string $text, array $params=[])
    {
        $this->data =['text' => $text];

        if(isset($params['request_users'])) {
            if (is_array($params['request_users'])){
                $this->data['request_users'] = $params['request_users'];
            } else {
                throw new \Exception("request_users 只能是数组");
            }
        } else if(isset($params['request_chat'])) {
            if (is_array($params['request_chat'])){
                $this->data['request_chat'] = $params['request_chat'];
            } else {
                throw new \Exception("request_chat 只能是数组");
            }
        } else if(isset($params['request_contact'])) {
            if (is_bool($params['request_contact'])) {
                $this->data['request_contact'] = $params['request_contact'];
            } else {
                throw new \Exception("request_contact 只能布尔类型");
            }
        } else if(isset($params['request_location'])) {
            if (is_bool($params['request_location'])) {
                $this->data['request_location'] = $params['request_location'];
            } else {
                throw new \Exception("request_location 只能布尔类型");
            }
        } else if(isset($params['request_poll'])) {
            if (is_array($params['request_poll'])){
                $this->data['request_poll'] = $params['request_poll'];
            } else {
                throw new \Exception("request_poll 只能是数组");
            }
        } else if(isset($params['web_app'])) {
            if (is_array($params['web_app'])){
                $this->data['web_app'] = $params['web_app'];
            } else {
                throw new \Exception("web_app 只能是数组");
            }
        }
    }

    public function make() :array
    {
        return $this->data;
    }

    public function delete()
    {
        $this->data = [];
    }
}
