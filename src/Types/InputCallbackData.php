<?php
namespace Telegram\Bot\Types;

class InputCallbackData
{
    public string $command;
    public array $args;

    public function __construct(string $command, string ...$args)
    {
        $this->command = $command;
        $this->args = $args;
    }

    public function make(): string
    {
        return $this->command .  implode(':', $this->args);;
    }
}
