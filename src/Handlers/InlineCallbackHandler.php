<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class InlineCallbackHandler
{
    public string $command;
    public int $group;
    public Filters $filters;
    public $handler;
    // public array $button;

    public function __construct(Filters $filters, callable $handler=null, int $group=0)
    {
        $filters->add(['inline_callback_message']);
        $this->group = $group;
        $this->filters = $filters;
        $this->handler = $handler;
    }
}