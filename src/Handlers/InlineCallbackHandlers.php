<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Types\Filters;

class InlineCallbackHandlers extends Handlers
{
    public function add($handler) {
        if ($handler instanceof InlineCallbackHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("InlineCallbackHandler 只能是 InlineCallbackHandler 类"); 
        }
    }
}