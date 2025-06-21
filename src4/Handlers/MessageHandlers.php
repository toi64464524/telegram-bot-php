<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Types\Filters;

class MessageHandlers extends Handlers
{
    public function add( $handler) {
        if ($handler instanceof MessageHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("MessageHandlers 只能是 MessageHandler 类"); 
        }
    }
}