<?php

namespace telegram\bot\types;

class CommandHandlers extends Handlers
{
    public function add( $handler) {
        if (!$handler instanceof CommandHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("CommandHandler 只能是 CommandHandler 类"); 
        }
    }
}