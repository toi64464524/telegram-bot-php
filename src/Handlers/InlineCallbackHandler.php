<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class InlineCallbackHandler extends Handler
{
    public function __construct(Filters $filters, callable $handler, int $group=0) {
        $filters->add(['inline_callback_message']);
        parent::__construct($filters, $handler, $group);
    }
}