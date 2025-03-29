<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Store;

abstract class Car
{
    public function __construct(public ?Key $key = null)
    {
    }

    public function putKey(Key $key): static
    {
        $this->key = $key;

        return $this;
    }
}
