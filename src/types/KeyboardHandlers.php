<?php

namespace telegram\bot\types;

// use App\Bot\Types\KeyboardHandler;
// use App\Bot\Types\KeyboardMarkup;

class KeyboardHandlers {
    public KeyboardMarkup $reply_markup;
    public array $handlers;

    public function __construct(array $handlers, bool $one_time_keyboard=False, bool $resize_keyboard=True, $is_persistent=true) 
    {
        $this->handlers = [];
        $commands = [];

        foreach ($handlers as $line) {
            $line_command = [];
            foreach ($line as $handler) {
                if ($handler instanceof KeyboardHandler) {
                    array_push($line_command, $handler->button);
                    array_push($this->handlers, $handler);
                }
            }
            array_push($commands, $line_command);
        }

        $this->reply_markup = new KeyboardMarkup($commands, $one_time_keyboard, $resize_keyboard, $is_persistent);
    }
}
