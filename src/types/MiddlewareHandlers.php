<?php

namespace telegram\bot\types;

class MiddlewareHandlers extends Handlers
{
    public function add( $handler) {
        if ($handler instanceof MiddlewareHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("MiddlewareHandler 只能是 MiddlewareHandler 类"); 
        }
    }
}