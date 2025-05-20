<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Types\Filters;

class KeyboardHandlers extends Handlers
{
    public function add($handler) {
        if ($handler instanceof KeyboardHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("KeyboardHandler 只能是 KeyboardHandler 类"); 
        }
    }
}
