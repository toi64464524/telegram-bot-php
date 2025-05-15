<?php

namespace telegram\bot\types;

class MiddlewareHandlers
{
    public array $handlers;

    public function __construct(array $handlers) {
        $this->handlers =[];

        foreach($handlers as $handler) {
            if ($handler instanceof MiddlewareHandler) {
                array_push($this->handlers, $handler);
            }else {
                throw new \Exception("MiddlewareHandlers 只能是 MiddlewareHandler 类"); 
            }
        }
    }

    public function add(MiddlewareHandler $handler) {
        if (!$handler instanceof MiddlewareHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("MiddlewareHandler 只能是 MiddlewareHandler 类"); 
        }
    }
}