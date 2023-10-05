<?php

namespace SergiX44\Hydrator;

interface Mutator
{
    public function mutate(mixed $value): mixed;
}
