<?php

namespace Telegram\Bot\Handlers;

use Telegram\Bot\Filters\Filters;

class Handler
{
    public int $group;
    protected Filters $filters;
    protected $handler;

    public function __construct(Filters $filters, callable $handler, int $group=0) {
        $this->filters = $filters;
        $this->group = $group;
        $this->handler = $handler;
    }

    /**
     * 获取过滤器
     */
    public function getFilters(): Filters {
        return $this->filters;
    }

    /**
     * 获取处理器
     */
    public function getHandler(): callable {
        return $this->handler;
    }

    /**
     * 获取组id
     */
    public function getGroup(): int {
        return $this->group;
    }
}