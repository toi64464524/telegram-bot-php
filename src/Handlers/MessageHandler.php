<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Types\Filters;

class MessageHandler
{
    public int $group;
    public Filters $filters;
    public $handler;

    public function __construct(Filters $filters, callable $handler, int $group=0) {
        $this->group = $group;
        $this->filters = $filters;
        $this->handler = $handler;
        $this->filters->add(['is_message']);
    }
}