<?php

namespace SergiX44\Hydrator\Mutation;

interface Mutator
{
    public function mutate(mixed $value): mixed;
}
