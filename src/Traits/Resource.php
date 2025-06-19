<?php

namespace Telegram\Bot\Traits;

use Telegram\Bot\Objects\Resource as ObjectsResource;

/**
 * Class Telegram.
 */
trait Resource
{
    /**
     * @var Api|null Telegram Api Instance.
     */
    protected ?ObjectsResource $resource = null;

    /**
     * Get Telegram Api Instance.
     */
    public function getResource(): ?ObjectsResource
    {
        return $this->resource;
    }

    /**
     * Set Telegram Api Instance.
     */
    public function setResource(?ObjectsResource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }
}
