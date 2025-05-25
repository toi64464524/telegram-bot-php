<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class MiddlewareHandler
{
    public int $group;
    public Filters $filters;
    public $handler;

    public function __construct(callable $handler, int $group=-1) {
        $this->group = $group;
        $this->filters = new Filters(['all']);
        $this->handler = $handler;
    }
}