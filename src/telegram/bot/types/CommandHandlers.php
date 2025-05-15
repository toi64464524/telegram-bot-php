<?php

namespace telegram\bot\types;

class CommandHandlers
{
    public array $commands;
    public array $handlers;
    
    public function __construct(array $handlers) {
        
        $this->handlers =[];
        $commands = [];
        foreach($handlers as $handler) {
            if ($handler instanceof CommandHandler) {
                array_push($commands, array('command' => $handler->command, 'description' => $handler->description));
                array_push($this->handlers, $handler);
            }else {
                throw new \Exception("CommandHandlers 只能是 CommandHandler 类"); 
            }
        }

        $this->commands =["commands"=>$commands];
    }

    public function add(CommandHandler $handler) {
        if (!$handler instanceof CommandHandler) {
            array_push($this->handlers, $handler);
        }else {
            throw new \Exception("CommandHandler 只能是 CommandHandler 类"); 
        }
    }
}