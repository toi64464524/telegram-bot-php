<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class KeyboardHandler extends Handler
{
    public function __construct(string $command, callable $handler=null,int $group=0)
    {
        parent::__construct(new Filters(["/^{$command}$/", "&", "message"]), $handler, $group);
    }
}
