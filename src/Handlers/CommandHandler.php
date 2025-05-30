<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class CommandHandler extends Handler
{
    public function __construct(string $command, callable $handler, int $group=0) {
        parent::__construct(new Filters(["/{$command}/", "&", "command_message"]), $handler, $group);
    }
}