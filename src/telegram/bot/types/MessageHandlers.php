<?php

namespace telegram\bot\types;

class MessageHandlers
{
    public array $handlers;

    public function __construct(array $handlers) {
        $this->handlers =[];

        foreach($handlers as $handler) {
            if ($handler instanceof MessageHandler) {
                array_push($this->handlers, $handler);
            }else {
                throw new \Exception("MessageHandlers 只能是 MessageHandler 类"); 
            }
        }
    }

    public function add(MessageHandler $handler) {
        if (!$handler instanceof MessageHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("MessageHandlers 只能是 MessageHandler 类"); 
        }
    }
}