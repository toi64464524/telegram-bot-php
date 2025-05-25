<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class KeyboardHandler 
{
    public string $command;
    public int $group;
    public Filters $filters;
    public $handler;
    public array $button;

    public function __construct(string $command, callable $handler=null,int $group=0)
    {
        $this->command = $command;
        $this->group = $group;
        $this->filters = new Filters(["/^{$command}$/", "&", "message"]);
        $this->handler = $handler;
    }
}
