<?php

namespace telegram\bot\types;

// use App\Bot\Types\KeyboardHandler;
// use App\Bot\Types\KeyboardMarkup;

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
