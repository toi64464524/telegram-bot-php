<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class CommandHandler
{
    public string $command;
    public string $description;
    public int $group;
    public Filters $filters;
    public $handler;

    public function __construct(string $command, callable $handler, int $group=0) {
        $this->command = $command;
        $this->handler = $handler;
        $this->group = $group;
        $this->filters = new Filters(["/{$command}/", "&", "command_message"]);
    }
}