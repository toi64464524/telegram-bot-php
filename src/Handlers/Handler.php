<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class Handler
{
    public int $group;
    public Filters $filters;
    public $handler;

    public function __construct(Filters $filters, callable $handler, int $group=0) {
        $this->group = $group;
        $this->handler = $handler;
    }
}