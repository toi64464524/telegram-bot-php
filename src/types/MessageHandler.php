<?php

namespace telegram\bot\types;

class MessageHandler
{
    public int $group;
    public Filters $filters;
    public $handler;

    public function __construct(Filters $filters, callable $handler, int $group=0) {
        $this->group = $group;
        $this->filters = $filters;
        $this->handler = $handler;
    }
}