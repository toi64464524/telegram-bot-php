<?php

namespace telegram\bot\types;

class MiddlewareHandler
{
    public int $group;
    public Filters $filters;
    public $handler;

    public function __construct(callable $handler, int $group=-1) {
        $this->group = $group;
        $this->filters = new Filters();
        $this->handler = $handler;
    }
}