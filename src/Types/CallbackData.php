<?php
namespace Telegram\Bot\Types;

use Telegram\Bot\Objects\CallbackQuery;

class CallbackData
{
    private array $data;
    public string $command;
    public array $args;

    public function __construct(?CallbackQuery $callbackQuery)
    {
        $this->data = $callbackQuery ? preg_split('/[| ,:]+/', $callbackQuery->data) : [];
    }

    public function getCommand(): string
    {
        return isset($this->data[0]) ? $this->data[0] : '';
    }
    
    public function getArgs(int $key): string
    {
        $args = isset($this->data[1]) ? $this->data[1] : [];
        return isset($args[$key]) ? $args[$key] : '';
    }
}
