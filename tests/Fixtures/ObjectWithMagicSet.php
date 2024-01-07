<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

class ObjectWithMagicSet
{
    public string $value = 'default';

    protected array $additional = [];

    public function __set(string $name, $value)
    {
        $this->additional[$name] = $value;
    }

    public function __get(string $name)
    {
        return $this->additional[$name] ?? null;
    }
}
