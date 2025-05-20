<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Types\Filters;

class InlineCallbackHandler
{
    public string $command;
    public int $group;
    public Filters $filters;
    public $handler;
    // public array $button;

    public function __construct(Filters $filters, callable $handler=null, int $group=0)
    {
        $filters->add(['is_inline_callback']);
        $this->group = $group;
        $this->filters = $filters;
        $this->handler = $handler;
    }
}