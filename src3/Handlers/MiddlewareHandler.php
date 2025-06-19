<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class MiddlewareHandler extends Handler
{
    public function __construct(callable $handler, int $group=-1) {
        parent::__construct(new Filters(['all']), $handler, $group);
    }
}