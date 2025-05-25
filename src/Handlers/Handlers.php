<?php

namespace Telegram\Bot\Handlers;

use IteratorAggregate;
use ArrayIterator;

class Handlers implements IteratorAggregate
{
    public array $handlers;

    public function __construct(array $handlers) {
        $this->handlers =[];
        foreach($handlers as $handler) {
            $this->add($handler);
        }
    }

    public function add(MiddlewareHandler|CommandHandler|InlineCallbackHandler|KeyboardHandler|StateHandler|MessageHandler $handler) 
    {
        array_push($this->handlers, $handler);
    }

    // 👇 实现 IteratorAggregate 接口，支持 foreach 遍历
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->handlers);
    }
}