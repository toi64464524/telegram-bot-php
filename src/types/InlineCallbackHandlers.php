<?php

namespace telegram\bot\types;

class InlineCallbackHandlers
{
    public array $handlers;
    
    public function __construct(array $handlers=[]) 
    {
        $this->handlers = [];
        foreach ($handlers as $handler) {
            // foreach ($line as $handler) {
                if ($handler instanceof InlineCallbackHandler) {
                    array_push($this->handlers, $handler);
                }
            // }
        }
    }

    public function add(InlineCallbackHandler $handler) {
        if (!$handler instanceof InlineCallbackHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("InlineCallbackHandler 只能是 InlineCallbackHandler 类"); 
        }
    }
}