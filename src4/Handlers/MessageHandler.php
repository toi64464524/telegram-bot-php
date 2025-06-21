<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class MessageHandler extends Handler
{
    public function __construct(Filters $filters, callable $handler, int $group=0) {
        parent::__construct($filters, $handler, $group);
    }
}