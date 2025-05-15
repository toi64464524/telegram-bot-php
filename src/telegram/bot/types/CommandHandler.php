<?php

namespace telegram\bot\types;

class CommandHandler
{
    public string $command;
    public string $description;
    public int $group;
    public Filters $filters;
    public $handler;

    public function __construct(string $command, string $description, callable $handler, int $group=0) {
        $this->command = $command;
        $this->description = $description;
        $this->handler = $handler;
        $this->group = $group;
        $this->filters = new Filters(['is_command', "/^\/{$command}$/"]);
    }
}