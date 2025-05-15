<?php

namespace telegram\bot\types;

class KeyboardHandler {
    public string $command;
    public int $group;
    public Filters $filters;
    public $handler;
    public array $button;

    public function __construct(string $command, array $params=[], callable $handler=null,int $group=0)
    {
        $this->command = $command;
        $this->group = $group;
        $this->filters = new Filters(["/^{$command}$/"]);
        $this->handler = $handler;
    }
}
