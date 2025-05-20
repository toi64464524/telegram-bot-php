<?php

namespace Telegram\Bot\Handlers;

class Handlers
{
    public array $handlers;

    public function __construct(array $handlers) {
        $this->handlers =[];
        foreach($handlers as $handler) {
            $this->add($handler);
        }
    }

    public function add(MiddlewareHandler|CommandHandler|InlineCallbackHandler|KeyboardHandler|StateHandler $handler) 
    {
        if (!$handler instanceof MiddlewareHandler && !$handler instanceof CommandHandler && !$handler instanceof InlineCallbackHandler && !$handler instanceof KeyboardHandler && !$handler instanceof StateHandler) {
            throw new \Exception("处理器类型错误"); 
        }
        array_push($this->handlers, $handler);
    }
}