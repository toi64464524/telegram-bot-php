<?php
namespace Telegram\Bot\Objects;

class CallbackQueryData
{
    private array $data;
    public string $command;
    public array $args;

    public function __construct(?CallbackQuery $callbackQuery)
    {
        $this->data = $callbackQuery ? preg_split('/[| ,:]+/', $callbackQuery->data) : [];
    }

    public function getCommand(): ?string
    {
        return isset($this->data[0]) ? $this->data[0] : null;
    }
    
    public function getArgs(int $key): ?string
    {
        return isset($this->data[$key + 1]) ? $this->data[$key + 1] : null;
    }
}
